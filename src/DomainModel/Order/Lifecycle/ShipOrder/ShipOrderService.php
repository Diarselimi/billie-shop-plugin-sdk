<?php

namespace App\DomainModel\Order\Lifecycle\ShipOrder;

use App\DomainModel\Invoice\InvoiceAnnouncer;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
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

    public function __construct(
        Registry $workflowRegistry,
        InvoiceAnnouncer $announcer,
        OrderInvoiceFactory $orderInvoiceFactory,
        OrderInvoiceRepositoryInterface $orderInvoiceRepository,
        OrderFinancialDetailsRepositoryInterface $orderFinancialDetailsRepository
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->announcer = $announcer;
        $this->orderInvoiceFactory = $orderInvoiceFactory;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->orderFinancialDetailsRepository = $orderFinancialDetailsRepository;
    }

    public function ship(OrderContainer $orderContainer, Invoice $invoice): void
    {
        $order = $orderContainer->getOrder();
        $workflow = $this->workflowRegistry->get($order);

        $orderContainer->addInvoice($invoice);

        $orderInvoice = $this->orderInvoiceFactory->create($order->getId(), $invoice->getUuid());
        $this->orderInvoiceRepository->insert($orderInvoice);

        $financialDetails = $orderContainer->getOrderFinancialDetails();
        $unshippedAmountGross = $financialDetails->getUnshippedAmountGross()->subtract($invoice->getAmount()->getGross());
        $unshippedAmountNet = $financialDetails->getUnshippedAmountNet()->subtract($invoice->getAmount()->getNet());
        $unshippedAmountTax = $financialDetails->getUnshippedAmountTax()->subtract($invoice->getAmount()->getTax());

        $financialDetails
            ->setUnshippedAmountGross($unshippedAmountGross)
            ->setUnshippedAmountNet($unshippedAmountNet)
            ->setUnshippedAmountTax($unshippedAmountTax)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
        ;

        $this->orderFinancialDetailsRepository->insert($financialDetails);

        $this->announcer->announce($invoice, $orderContainer->getDebtorCompany()->getName());

        if ($order->isWorkflowV2()) {
            $isFullyShipped = $unshippedAmountGross->isZero() && $unshippedAmountNet->isZero() && $unshippedAmountTax->isZero();
            $transition = $isFullyShipped ? OrderEntity::TRANSITION_SHIP_FULLY : OrderEntity::TRANSITION_SHIP_PARTIALLY;

            $workflow->apply($order, $transition);
        }

        $this->logInfo('Order shipped with {name} workflow', [LoggingInterface::KEY_NAME => $workflow->getName()]);
    }
}
