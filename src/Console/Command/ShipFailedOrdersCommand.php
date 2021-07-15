<?php

namespace App\Console\Command;

use App\DomainModel\Invoice\InvoiceAnnouncer;
use App\DomainModel\Invoice\InvoiceFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
use Billie\PdoBundle\Infrastructure\Pdo\PdoConnection;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShipFailedOrdersCommand extends Command
{
    protected static $defaultName = 'paella:order:ship-failed';

    private InvoiceFactory $invoiceFactory;

    private PdoConnection $db;

    private InvoiceAnnouncer $announcer;

    private InvoiceDocumentUploadHandlerAggregator $invoiceManager;

    private OrderContainerFactory $orderContainerFactory;

    public function __construct(InvoiceFactory $invoiceFactory, PdoConnection $db, InvoiceAnnouncer $announcer, InvoiceDocumentUploadHandlerAggregator $invoiceManager, OrderContainerFactory $orderContainerFactory)
    {
        parent::__construct();

        $this->invoiceFactory = $invoiceFactory;
        $this->db = $db;
        $this->announcer = $announcer;
        $this->invoiceManager = $invoiceManager;
        $this->orderContainerFactory = $orderContainerFactory;
    }

    protected function configure()
    {
        $this
            ->setDescription('Ship failed orders')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stmt = $this->db->query('
            select orders.id from orders
            left join borscht.tickets on orders.payment_id = borscht.tickets.uuid
            where orders.payment_id is not null and borscht.tickets.uuid is null;
        ');

        while ($item = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $orderContainer = $this->orderContainerFactory->loadById((int) $item['id']);
            $financialDetails = $orderContainer->getOrderFinancialDetails();

            $invoice = $this->invoiceFactory->create(
                $orderContainer,
                new TaxedMoney(
                    $financialDetails->getAmountGross(),
                    $financialDetails->getAmountNet(),
                    $financialDetails->getAmountTax()
                ),
                $orderContainer->getOrderFinancialDetails()->getDuration(),
                $orderContainer->getOrder()->getInvoiceNumber(),
                $orderContainer->getOrder()->getProofOfDeliveryUrl(),
                $orderContainer->getOrder()->getPaymentId()
            );

            $this->announcer->announce($invoice, $orderContainer->getDebtorCompany()->getName(), $orderContainer->getOrder()->getExternalCode());

            $this->invoiceManager->handle(
                $orderContainer->getOrder(),
                $invoice->getUuid(),
                $orderContainer->getOrder()->getInvoiceUrl(),
                $orderContainer->getOrder()->getInvoiceNumber(),
                InvoiceDocumentUploadHandlerAggregator::EVENT_SOURCE_SHIPMENT
            );

            $output->writeln("Order {$orderContainer->getOrder()->getId()} processed");
        }

        return 0;
    }
}
