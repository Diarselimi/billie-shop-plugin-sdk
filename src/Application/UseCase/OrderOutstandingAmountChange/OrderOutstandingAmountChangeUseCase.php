<?php

namespace App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\LimitsService;
use App\DomainModel\OrderNotification\NotificationScheduler;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Raven_Client;

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

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        NotificationScheduler $notificationScheduler,
        Raven_Client $sentry,
        LimitsService $limitsService
    ) {
        $this->orderRepository = $orderRepository;
        $this->merchantRepository = $merchantRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->notificationScheduler = $notificationScheduler;
        $this->sentry = $sentry;
        $this->limitsService = $limitsService;
    }

    public function execute(OrderOutstandingAmountChangeRequest $request)
    {
        $orderAmountChangeDetails = $request->getOrderAmountChangeDetails();
        $order = $this->orderRepository->getOneByPaymentId($orderAmountChangeDetails->getId());

        if (!$order) {
            $this->logError(
                '[suppressed] Trying to change state for non-existing order',
                [
                    'payment_id' => $orderAmountChangeDetails->getId(),
                ]
            );

            $this->sentry->captureException(new PaellaCoreCriticalException('Order not found'));

            return;
        }

        $merchant = $this->merchantRepository->getOneById($order->getMerchantId());
        if (is_null($merchant)) {
            throw new MerchantNotFoundException();
        }

        $merchant->increaseAvailableFinancingLimit($orderAmountChangeDetails->getAmountChange());

        $merchantDebtor = $this->merchantDebtorRepository->getOneById($order->getMerchantDebtorId());

        if (!$merchantDebtor) {
            $this->logError(
                '[suppressed] Merchant Debtor not found.',
                [
                    'payment_id' => $order->getId(),
                    'merchant_debtor_id' => $order->getMerchantDebtorId(),
                ]
            );
            $this->sentry->captureException(
                new PaellaCoreCriticalException('Merchant Debtor not found for order #' . $order->getId())
            );

            return;
        }

        $this->limitsService->unlock($merchantDebtor, $orderAmountChangeDetails->getAmountChange());

        $this->merchantRepository->update($merchant);

        if (!$orderAmountChangeDetails->isPayment()) {
            return;
        }

        $payload = [
            'event' => self::NOTIFICATION_EVENT,
            'order_id' => $order->getExternalCode(),
            'amount' => $orderAmountChangeDetails->getPaidAmount(),
            'open_amount' => $orderAmountChangeDetails->getOutstandingAmount(),
        ];

        $this->notificationScheduler->createAndSchedule($order, $payload);
    }
}
