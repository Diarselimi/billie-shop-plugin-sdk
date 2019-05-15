<?php

namespace App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Borscht\OrderAmountChangeDTO;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
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

    private $orderPersistenceService;

    private $notificationScheduler;

    private $limitsService;

    private $paymentForgivenessService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        OrderPersistenceService $orderPersistenceService,
        NotificationScheduler $notificationScheduler,
        MerchantDebtorLimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService
    ) {
        $this->orderRepository = $orderRepository;
        $this->merchantRepository = $merchantRepository;
        $this->orderPersistenceService = $orderPersistenceService;
        $this->notificationScheduler = $notificationScheduler;
        $this->limitsService = $limitsService;
        $this->paymentForgivenessService = $paymentForgivenessService;
    }

    public function execute(OrderOutstandingAmountChangeRequest $request)
    {
        $amountChange = $request->getOrderAmountChangeDetails();

        $order = $this->orderRepository->getOneByPaymentId($amountChange->getId());
        if (!$order) {
            $this->logSuppressedException(
                new OrderNotFoundException(),
                '[suppressed] Trying to change state for non-existing order',
                ['payment_id' => $amountChange->getId()]
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

        $orderContainer = $this->orderPersistenceService->createFromOrderEntity($order);
        $merchant = $orderContainer->getMerchant();

        $this->limitsService->unlock($orderContainer, $amountChange->getAmountChange());
        $merchant->increaseAvailableFinancingLimit($amountChange->getAmountChange());
        $this->merchantRepository->update($merchant);

        if (!$amountChange->isPayment()) {
            return;
        }

        $this->scheduleEvent($order, $amountChange);

        if ($this->paymentForgivenessService->begForgiveness($order, $amountChange)) {
            $this->logInfo(
                "Order {id} outstanding amount of {amount} will be forgiven and paid by the merchant {merchant_id}",
                [
                    'id' => $order->getId(),
                    'amount' => $amountChange->getOutstandingAmount(),
                    'merchant_id' => $merchant->getId(),
                ]
            );
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
