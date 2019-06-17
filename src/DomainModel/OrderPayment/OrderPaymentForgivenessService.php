<?php

namespace App\DomainModel\OrderPayment;

use App\DomainModel\Borscht\BorschtInterface;
use App\DomainModel\Borscht\OrderAmountChangeDTO;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class OrderPaymentForgivenessService implements LoggingInterface
{
    use LoggingTrait;

    private $paymentsService;

    private $orderRepository;

    public function __construct(
        BorschtInterface $paymentsService,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->paymentsService = $paymentsService;
        $this->orderRepository = $orderRepository;
    }

    public function begForgiveness(OrderContainer $orderContainer, OrderAmountChangeDTO $amountChange): bool
    {
        $order = $orderContainer->getOrder();
        if ($order->getAmountForgiven() > 0) {
            return false;
        }

        $outstandingAmount = $amountChange->getOutstandingAmount();
        $debtorForgivenessThreshold = $orderContainer->getMerchantSettings()->getDebtorForgivenessThreshold();

        $this->logInfo('Begging for forgiveness of {outstanding_amount} in order {order_id}', [
            'order_id' => $order->getId(),
            'forgiveness_threshold' => $debtorForgivenessThreshold,
            'outstanding_amount' => $outstandingAmount,
            'paid_amount' => $amountChange->getPaidAmount(),
            'amount_higher_than_threshold' => $outstandingAmount > $debtorForgivenessThreshold,
        ]);

        if (
            ($debtorForgivenessThreshold <= 0) ||
            ($outstandingAmount <= 0) ||
            ($amountChange->getPaidAmount() <= 0) ||
            ($outstandingAmount > $debtorForgivenessThreshold)
        ) {
            return false;
        }

        $this->paymentsService->confirmPayment($order, $outstandingAmount);

        $order->setAmountForgiven($outstandingAmount);
        $this->orderRepository->update($order);

        return true;
    }
}
