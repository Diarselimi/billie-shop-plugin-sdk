<?php

namespace App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationPayloadFactory;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderNotification\NotificationScheduler;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class OrderOutstandingAmountChangeUseCase implements LoggingInterface
{
    use LoggingTrait;

    private MerchantRepositoryInterface $merchantRepository;

    private OrderContainerFactory $orderContainerFactory;

    private NotificationScheduler $notificationScheduler;

    private MerchantDebtorLimitsService $limitsService;

    private Registry $workflowRegistry;

    private OrderNotificationPayloadFactory $orderEventPayloadFactory;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        Registry $workflowRegistry,
        OrderNotificationPayloadFactory $orderEventPayloadFactory
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->merchantRepository = $merchantRepository;
        $this->notificationScheduler = $notificationScheduler;
        $this->limitsService = $limitsService;
        $this->workflowRegistry = $workflowRegistry;
        $this->orderEventPayloadFactory = $orderEventPayloadFactory;
    }

    public function execute(OrderOutstandingAmountChangeRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->createFromInvoiceId($request->getId());
        } catch (OrderContainerFactoryException $exception) {
            $this->logSuppressedException(
                new OrderNotFoundException(),
                '[suppressed] Trying to change state for non-existing order',
                ['payment_id' => $request->getId()]
            );

            return;
        }

        $order = $orderContainer->getOrder();

        if (!$order->wasShipped() && !$order->isCanceled()) {
            $this->logSuppressedException(
                new WorkflowException('Order amount change not possible'),
                '[suppressed] Outstanding amount change not possible for order {order_id}',
                [
                    'order_id' => $order->getId(),
                    'state' => $order->getState(),
                ]
            );

            return;
        }

        $merchant = $orderContainer->getMerchant();

        if ($request->getAmountChange()->greaterThan(0)) {
            try {
                $this->limitsService->unlock($orderContainer, $request->getAmountChange());
            } catch (MerchantDebtorLimitsException $exception) {
                $this->logSuppressedException($exception, 'Limes call failed', ['exception' => $exception]);
            }
        }

        $merchant->increaseFinancingLimit($request->getAmountChange());
        $this->merchantRepository->update($merchant);

        if ($request->getType() !== OrderOutstandingAmountChangeRequest::TYPE_PAYMENT) {
            return;
        }

        $this->scheduleMerchantNotification($order, $request);
    }

    private function scheduleMerchantNotification(
        OrderEntity $order,
        OrderOutstandingAmountChangeRequest $request
    ): void {
        $this->notificationScheduler->createAndSchedule(
            $order,
            OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
            $this->orderEventPayloadFactory->create(
                $order,
                OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
                [
                    'amount' => $request->getPaidAmount()->toFloat(),
                    'open_amount' => $request->getOutstandingAmount()->toFloat(),
                    'iban' => $request->getIban(),
                    'account_holder' => $request->getAccountHolder(),
                ]
            )
        );
    }
}
