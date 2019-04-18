<?php

namespace App\Application\UseCase\OrderTriggerInvoiceDownload;

use App\DomainModel\Order\OrderRepositoryInterface;
use App\Infrastructure\Sns\SnsInvoiceUploadHandler;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class OrderTriggerInvoiceDownloadUseCase implements LoggingInterface
{
    use LoggingTrait;

    private const INVOICE_URL_TEMPLATE = '%sBillie_Invoice_%s.pdf';

    private $orderRepository;

    private $invoiceHandler;

    public function __construct(OrderRepositoryInterface $orderRepository, SnsInvoiceUploadHandler $invoiceHandler)
    {
        $this->orderRepository = $orderRepository;
        $this->invoiceHandler = $invoiceHandler;
    }

    public function execute(int $limit, int $batchSize, int $sleepTime, int $lastId = 0, string $basePath = '/'): int
    {
        $orders = $this->orderRepository->getWithInvoiceNumber($limit, $lastId);

        foreach ($orders as $index => $orderRow) {
            if ($index !== 0 && $index % $batchSize === 0) {
                sleep($sleepTime);
            }

            /** @var array $orderRow */
            $merchantId = $orderRow['merchant_id'];
            $orderId = $orderRow['id'];
            $orderExternalCode = $orderRow['external_code'];
            $invoiceNumber = $orderRow['invoice_number'];

            $this->invoiceHandler->handleInvoice(
                $orderExternalCode,
                $merchantId,
                $invoiceNumber,
                sprintf(self::INVOICE_URL_TEMPLATE, $basePath, $invoiceNumber),
                SnsInvoiceUploadHandler::EVENT_MIGRATION
            );

            $lastId = $orderId;
        }

        return $lastId;
    }
}
