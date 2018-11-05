<?php

namespace App\Application\UseCase\OrderOutstandingAmountChange;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Webhook\NotificationDTO;
use App\DomainModel\Webhook\NotificationSender;
use Raven_Client;

class OrderOutstandingAmountChangeUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $orderRepository;

    private $merchantRepository;

    private $merchantDebtorRepository;

    private $notificationSender;

    private $sentry;

    private $alfred;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        NotificationSender $notificationSender,
        Raven_Client $sentry,
        AlfredInterface $alfred
    ) {
        $this->orderRepository = $orderRepository;
        $this->merchantRepository = $merchantRepository;
        $this->merchantDebtorRepository = $merchantDebtorRepository;
        $this->notificationSender = $notificationSender;
        $this->sentry = $sentry;
        $this->alfred = $alfred;
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

        $this->alfred->unlockDebtorLimit($merchantDebtor->getDebtorId(), $orderAmountChangeDetails->getAmountChange());

        $this->merchantRepository->update($merchant);

        if (!$orderAmountChangeDetails->isPayment()) {
            return;
        }

        $notification = (new NotificationDTO())
            ->setEventName(NotificationDTO::EVENT_PAYMENT)
            ->setOrderId($order->getExternalCode())
            ->setAmount($orderAmountChangeDetails->getPaidAmount())
            ->setOpenAmount($orderAmountChangeDetails->getOutstandingAmount())
        ;

        $this->notificationSender->send($merchant, $notification);
    }
}
