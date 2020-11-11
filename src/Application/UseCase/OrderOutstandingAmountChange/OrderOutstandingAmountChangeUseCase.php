<?php

namespace App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationPayloadFactory;
use App\DomainModel\Payment\OrderAmountChangeDTO;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderNotification\NotificationScheduler;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\Money;
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
        $amountChange = $request->getOrderAmountChangeDetails();

        try {
            $orderContainer = $this->orderContainerFactory->createFromPaymentId($amountChange->getId());
        } catch (OrderContainerFactoryException $exception) {
            $this->logSuppressedException(
                new OrderNotFoundException(),
                '[suppressed] Trying to change state for non-existing order',
                ['payment_id' => $amountChange->getId()]
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

        if ($amountChange->getAmountChange() > 0) {
            try {
                $this->limitsService->unlock($orderContainer, new Money($amountChange->getAmountChange()));
            } catch (MerchantDebtorLimitsException $exception) {
                $this->logSuppressedException($exception, 'Limes call failed', ['exception' => $exception]);
            }
        }

        $merchant->increaseFinancingLimit(new Money($amountChange->getAmountChange()));
        $this->merchantRepository->update($merchant);

        if (!$amountChange->isPayment()) {
            return;
        }

        $this->scheduleMerchantNotification($order, $amountChange);

        if ($amountChange->getOutstandingAmount() <= 0 && !$order->isCanceled()) {
            $this->workflowRegistry->get($order)->apply($order, OrderEntity::TRANSITION_COMPLETE);
        }
    }

    private function scheduleMerchantNotification(OrderEntity $order, OrderAmountChangeDTO $amountChange): void
    {
        $this->notificationScheduler->createAndSchedule(
            $order,
            OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
            $this->orderEventPayloadFactory->create(
                $order,
                OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
                [
                    'amount' => $amountChange->getPaidAmount(),
                    'open_amount' => $amountChange->getOutstandingAmount(),
                    'iban' => $amountChange->getIban(),
                    'account_holder' => $amountChange->getAccountHolder(),
                ]
            )
        );
    }
}
