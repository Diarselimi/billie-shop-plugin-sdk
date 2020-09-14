<?php

namespace App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainEvent\Order\OrderEventPayloadFactory;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\Payment\OrderAmountChangeDTO;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderPayment\OrderPaymentForgivenessService;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\Money;

class OrderOutstandingAmountChangeUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $merchantRepository;

    private $orderContainerFactory;

    private $notificationScheduler;

    private $limitsService;

    private $paymentForgivenessService;

    private $orderStateManager;

    private $orderEventPayloadFactory;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService,
        OrderStateManager $orderStateManager,
        OrderEventPayloadFactory $orderEventPayloadFactory
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->merchantRepository = $merchantRepository;
        $this->notificationScheduler = $notificationScheduler;
        $this->limitsService = $limitsService;
        $this->paymentForgivenessService = $paymentForgivenessService;
        $this->orderStateManager = $orderStateManager;
        $this->orderEventPayloadFactory = $orderEventPayloadFactory;
    }

    public function execute(OrderOutstandingAmountChangeRequest $request)
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

        if (!$this->orderStateManager->wasShipped($order) && !$this->orderStateManager->isCanceled($order)) {
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

        /**
         * This check is deprecated and will be removed, no Money refactoring needed
         * @deprecated
         */
        if ($order->getAmountForgiven() > 0 && $amountChange->getOutstandingAmount() > 0) {
            $this->logInfo("Order {id} has been already forgiven by a total of {number}. Current outstanding amount is {count}.", [
                LoggingInterface::KEY_ID => $order->getId(),
                LoggingInterface::KEY_NUMBER => $order->getAmountForgiven(),
                LoggingInterface::KEY_COUNT => $amountChange->getOutstandingAmount(),
            ]);

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

        if ($this->paymentForgivenessService->begForgiveness($orderContainer, $amountChange)) {
            $this->logInfo(
                "Order {id} outstanding amount of {count} will be forgiven and paid by the merchant {number}",
                [
                    LoggingInterface::KEY_ID => $order->getId(),
                    LoggingInterface::KEY_COUNT => $amountChange->getOutstandingAmount(),
                    LoggingInterface::KEY_NUMBER => $merchant->getId(),
                ]
            );
        }

        if ($amountChange->getOutstandingAmount() <= 0 && !$this->orderStateManager->isCanceled($order)) {
            $this->orderStateManager->complete($orderContainer);
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
                ]
            )
        );
    }
}
