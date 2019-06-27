<?php

namespace App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\DomainModel\Borscht\OrderAmountChangeDTO;
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

class OrderOutstandingAmountChangeUseCase implements LoggingInterface
{
    use LoggingTrait;

    private const NOTIFICATION_EVENT = 'payment';

    private $orderRepository;

    private $merchantRepository;

    private $orderContainerFactory;

    private $notificationScheduler;

    private $limitsService;

    private $paymentForgivenessService;

    private $orderStateManager;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        MerchantRepositoryInterface $merchantRepository,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService,
        OrderStateManager $orderStateManager
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->merchantRepository = $merchantRepository;
        $this->notificationScheduler = $notificationScheduler;
        $this->limitsService = $limitsService;
        $this->paymentForgivenessService = $paymentForgivenessService;
        $this->orderStateManager = $orderStateManager;
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

        if (!$this->orderStateManager->wasShipped($orderContainer->getOrder())) {
            $this->logSuppressedException(
                new OrderWorkflowException('Order state change not possible'),
                '[suppressed] Outstanding amount change not possible for order {order_id}',
                [
                    'order_id' => $order->getId(),
                    'state' => $order->getState(),
                ]
            );

            return;
        }

        if ($order->getAmountForgiven() > 0 && $amountChange->getOutstandingAmount() > 0) {
            $this->logInfo("Order {id} has been already forgiven by a total of {amount}. Current outstanding amount is {outstanding_amount}.", [
                'id' => $order->getId(),
                'amount' => $order->getAmountForgiven(),
                'outstanding_amount' => $amountChange->getOutstandingAmount(),
            ]);

            return;
        }

        $merchant = $orderContainer->getMerchant();

        try {
            $this->limitsService->unlock($orderContainer, $amountChange->getAmountChange());
        } catch (MerchantDebtorLimitsException $exception) {
            $this->logSuppressedException($exception, 'Amazing merchant payment borscht bug');
        }

        $merchant->increaseAvailableFinancingLimit($amountChange->getAmountChange());
        $this->merchantRepository->update($merchant);

        if (!$amountChange->isPayment()) {
            return;
        }

        $this->scheduleEvent($order, $amountChange);

        if ($this->paymentForgivenessService->begForgiveness($orderContainer, $amountChange)) {
            $this->logInfo(
                "Order {id} outstanding amount of {amount} will be forgiven and paid by the merchant {merchant_id}",
                [
                    'id' => $order->getId(),
                    'amount' => $amountChange->getOutstandingAmount(),
                    'merchant_id' => $merchant->getId(),
                ]
            );
        }

        if ($amountChange->getOutstandingAmount() <= 0) {
            $this->orderStateManager->complete($orderContainer);
        }
    }

    private function scheduleEvent(OrderEntity $order, OrderAmountChangeDTO $amountChange)
    {
        $payload = [
            'event' => self::NOTIFICATION_EVENT,
            'order_id' => $order->getExternalCode(),
            'amount' => $amountChange->getPaidAmount(),
            'open_amount' => $amountChange->getOutstandingAmount(),
        ];

        $this->notificationScheduler->createAndSchedule($order, $payload);
    }
}
