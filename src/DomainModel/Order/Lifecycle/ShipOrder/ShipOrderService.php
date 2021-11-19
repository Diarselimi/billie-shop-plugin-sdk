<?php

namespace App\DomainModel\Order\Lifecycle\ShipOrder;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceAnnouncer;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceFactory;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class ShipOrderService implements ShipOrderInterface, LoggingInterface
{
    use LoggingTrait;

    private Registry $workflowRegistry;

    private InvoiceAnnouncer $announcer;

    private OrderInvoiceFactory $orderInvoiceFactory;

    private OrderInvoiceRepositoryInterface $orderInvoiceRepository;

    private OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository;

    private OrderRepository $orderRepository;

    public function __construct(
        Registry $workflowRegistry,
        InvoiceAnnouncer $announcer,
        OrderInvoiceFactory $orderInvoiceFactory,
        OrderInvoiceRepositoryInterface $orderInvoiceRepository,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository,
        OrderRepository $orderRepository
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->announcer = $announcer;
        $this->orderInvoiceFactory = $orderInvoiceFactory;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->orderFinancialDetailsRepository = $orderFinancialDetailsRepository;
        $this->orderRepository = $orderRepository;
    }

    public function ship(OrderContainer $orderContainer, Invoice $invoice): void
    {
        $order = $orderContainer->getOrder();
        $workflow = $this->workflowRegistry->get($order);
        $orderContainer->addInvoice($invoice);

        $orderInvoice = $this->orderInvoiceFactory->create($order->getId(), $invoice->getUuid());
        $this->orderInvoiceRepository->insert($orderInvoice);

        $financialDetails = $orderContainer->getOrderFinancialDetails();
        $unshippedAmountGross = $financialDetails->getUnshippedAmountGross()->subtract(
            $invoice->getAmount()->getGross()
        );
        $unshippedAmountNet = $financialDetails->getUnshippedAmountNet()->subtract($invoice->getAmount()->getNet());
        $unshippedAmountTax = $financialDetails->getUnshippedAmountTax()->subtract($invoice->getAmount()->getTax());

        $financialDetails
            ->setUnshippedAmountGross($unshippedAmountGross)
            ->setUnshippedAmountNet($unshippedAmountNet)
            ->setUnshippedAmountTax($unshippedAmountTax)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $this->orderFinancialDetailsRepository->insert($financialDetails);

        $this->announcer->announce(
            $invoice,
            $orderContainer->getOrder()->getUuid(),
            $orderContainer->getDebtorCompany()->getName(),
            $orderContainer->getOrder()->getExternalCode(),
            $orderContainer->getOrder()->getDebtorSepaMandateUuid(),
            $orderContainer->getMerchantDebtor()->getInvestorUuid()
        );

        //TODO simplify this with a guard
        if ($order->isWorkflowV2()) {
            $isFullyShipped = $unshippedAmountGross->isZero() && $unshippedAmountNet->isZero()
                && $unshippedAmountTax->isZero();
            $transition = $isFullyShipped ? OrderEntity::TRANSITION_SHIP_FULLY : OrderEntity::TRANSITION_SHIP_PARTIALLY;

            $workflow->apply($order, $transition);
        } elseif ($order->isWorkflowV1()) {
            $this->updateOrderPaymentUuid($order, $invoice);
            $workflow->apply($order, OrderEntity::TRANSITION_SHIP);
        }

        $this->logInfo('Order shipped with {name} workflow', [LoggingInterface::KEY_NAME => $workflow->getName()]);
    }

    private function updateOrderPaymentUuid(OrderEntity $order, Invoice $invoice): void
    {
        if ($order->getPaymentId() === null) {
            $order
                ->setPaymentId($invoice->getPaymentUuid())
                ->setShippedAt(new \DateTime());
            $this->orderRepository->update($order);
        }
    }
}
