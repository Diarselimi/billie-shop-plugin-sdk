<?php

namespace App\DomainModel\Order\Lifecycle\ShipOrder;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Payment\OrderPaymentDetailsDTO;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class LegacyShipOrderService implements ShipOrderInterface, LoggingInterface
{
    use LoggingTrait;

    private Registry $workflowRegistry;

    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        Registry $workflowRegistry,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->orderRepository = $orderRepository;
    }

    public function ship(OrderContainer $orderContainer, Invoice $invoice): void
    {
        $order = $orderContainer->getOrder();
        $workflow = $this->workflowRegistry->get($order);

        if ($order->getPaymentId() === null) {
            if (!$this->isPartialShipment($orderContainer, $invoice)) {
                $order
                    ->setPaymentId($invoice->getPaymentUuid())
                    ->setShippedAt(new \DateTime());
            }
            $this->orderRepository->update($order);
        }

        if ($order->isWorkflowV1()) {
            $workflow->apply($order, OrderEntity::TRANSITION_SHIP);
        }

        $orderGrossAmount = $orderContainer->getOrderFinancialDetails()->getAmountGross()->toFloat();
        $orderPaymentDetails = (new OrderPaymentDetailsDTO())
            ->setPayoutAmount($orderGrossAmount)
            ->setOutstandingAmount($orderGrossAmount)
            ->setOutstandingAmountInvoiceCancellation(0)
            ->setOutstandingAmountMerchantPayment(0)
            ->setFeeRate($invoice->getFeeRate()->toFloat())
            ->setFeeAmount($invoice->getFeeAmount()->getGross()->toFloat())
        ;

        $orderContainer->setPaymentDetails($orderPaymentDetails);

        $this->logInfo('Order shipped with {name} workflow', [LoggingInterface::KEY_NAME => $workflow->getName()]);
    }

    private function isPartialShipment(OrderContainer $orderContainer, Invoice $invoice): bool
    {
        return !$orderContainer->getOrderFinancialDetails()->getAmountGross()->equals(
            $invoice->getAmount()->getGross()
        );
    }
}
