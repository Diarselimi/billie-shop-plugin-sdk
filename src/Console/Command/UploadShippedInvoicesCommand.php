<?php

declare(strict_types=1);

namespace App\Console\Command;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
use App\Infrastructure\OrderInvoice\SnsInvoiceDocumentUploadHandler;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UploadShippedInvoicesCommand extends Command implements LoggingInterface
{
    use LoggingTrait;

    private const OPTION_LIMIT = 'limit';

    private const OPTION_SHIPPED_FROM = 'shipped_from';

    private const ARGUMENT_MERCHANT_ID = 'merchant_id';

    private const DEFAULT_LIMIT = 1000;

    protected static $defaultName = 'paella:invoices:upload';

    private SnsInvoiceDocumentUploadHandler $invoiceManager;

    private OrderRepository $orderRepository;

    private OrderContainerFactory $orderContainerFactory;

    public function __construct(
        SnsInvoiceDocumentUploadHandler $invoiceManager,
        OrderRepository $orderRepository,
        OrderContainerFactory $orderContainerFactory
    ) {
        $this->invoiceManager = $invoiceManager;
        $this->orderRepository = $orderRepository;
        $this->orderContainerFactory = $orderContainerFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption(self::OPTION_LIMIT, 'l', InputOption::VALUE_OPTIONAL, '', self::DEFAULT_LIMIT)
            ->addOption(self::OPTION_SHIPPED_FROM, 'f', InputOption::VALUE_OPTIONAL, '', (new \DateTime())->sub(new \DateInterval('P50D'))->format('c'))
            ->addArgument(self::ARGUMENT_MERCHANT_ID, InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $merchantId = $input->getArgument(self::ARGUMENT_MERCHANT_ID);
        $shippedFrom = new \DateTime($input->getOption(self::OPTION_SHIPPED_FROM));

        $this->logInfo(
            sprintf(
                'Starting the invoices upload for the merchant "%d" shipped from "%s"',
                $merchantId,
                $shippedFrom->format('c')
            )
        );

        $orders = $this->orderRepository->geOrdersByMerchantId(
            (int) $merchantId,
            new \DateTime($input->getOption(self::OPTION_SHIPPED_FROM)),
            (int) $input->getOption(self::OPTION_LIMIT)
        );

        $this->logInfo(
            sprintf(
                'Found "%d" orders',
                count($orders)
            )
        );

        foreach ($orders as $order) {
            $container = $this->orderContainerFactory->createFromOrderEntity($order);
            $invoices = $container->getInvoices();

            /** @var Invoice $invoice */
            foreach ($invoices as $invoice) {
                $this->uploadInvoice(
                    $order,
                    $invoice->getUuid(),
                    $order->getInvoiceUrl(),
                    $invoice->getExternalCode()
                );
            }
        }

        return 0;
    }

    private function uploadInvoice(
        OrderEntity $order,
        string $invoiceUuid,
        ?string $invoiceUrl,
        string $invoiceNumber
    ): void {
        if ($invoiceUrl === null) {
            $this->logInfo(
                sprintf(
                    'Uploading order #%d. Skip. Invoice URL is null',
                    $order->getId()
                )
            );

            return;
        }

        $this->logInfo(
            sprintf(
                'Uploading order #%d. Invoice url "%s"',
                $order->getId(),
                $invoiceUrl
            )
        );

        $this->invoiceManager->handle(
            $order,
            $invoiceUuid,
            $invoiceUrl,
            $invoiceNumber,
            InvoiceDocumentUploadHandlerAggregator::EVENT_SOURCE_SHIPMENT
        );
    }
}
