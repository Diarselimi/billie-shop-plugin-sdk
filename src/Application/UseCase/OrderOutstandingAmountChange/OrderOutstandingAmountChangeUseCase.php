<?php

namespace App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Borscht\OrderAmountChangeDTO;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\LimitsService;
use App\DomainModel\Order\OrderEntity;
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

    private $merchantDebtorRepository;

    private $notificationScheduler;

    private $sentry;

    private $limitsService;

    private $paymentForgivenessService;

    private $merchantSettingsRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        NotificationScheduler $notificationScheduler,
        LimitsService $limitsService,
        OrderPaymentForgivenessService $paymentForgivenessService
    ) {
        $this->orderRepository = $orderRepository;
        $this->merchantRepository = $merchantRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
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
                new PaellaCoreCriticalException('Order not found'),
                '[suppressed] Trying to change state for non-existing order',
                ['payment_id' => $amountChange->getId()]
            );

            return;
        }

        if ($order->getAmountForgiven() > 0 && $amountChange->getOutstandingAmount() > 0) {
            $this->logInfo(
                "Order {id} has been already forgiven by a total of {amount}. Current outstanding amount is {outstanding_amount}.",
                [
                    'id' => $order->getId(),
                    'amount' => $order->getAmountForgiven(),
                    'outstanding_amount' => $amountChange->getOutstandingAmount(),
                ]
            );

            return;
        }

        $merchant = $this->merchantRepository->getOneById($order->getMerchantId());
        if (!$merchant) {
            $this->logSuppressedException(
                new PaellaCoreCriticalException('Merchant not found for order #' . $order->getId()),
                '[suppressed] Merchant not found.',
                ['payment_id' => $order->getId(), 'merchant_id' => $order->getMerchantId()]
            );

            return;
        }

        $merchant->increaseAvailableFinancingLimit($amountChange->getAmountChange());

        $merchantDebtor = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());
        if (!$merchantDebtor) {
            $this->logSuppressedException(
                new PaellaCoreCriticalException('Merchant Debtor not found for order #' . $order->getId()),
                '[suppressed] Merchant Debtor not found.',
                ['payment_id' => $order->getId(), 'merchant_debtor_id' => $order->getMerchantDebtorId()]
            );

            return;
        }

        $this->limitsService->unlock($merchantDebtor, $amountChange->getAmountChange());
        $this->merchantRepository->update($merchant);

        if (!$amountChange->isPayment()) {
            return;
        }

        $this->scheduleEvent($order, $amountChange);

        if ($this->paymentForgivenessService->begForgiveness($order, $amountChange)) {
            $this->logInfo(
                "Order {id} outstanding amount of {amount} will be forgiven and paid by the merchant {merchant}",
                [
                    'id' => $order->getId(),
                    'amount' => $amountChange->getOutstandingAmount(),
                    'merchant' => $merchant->getId(),
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
