<?php

namespace App\Infrastructure\OrderInvoice;

use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadRequest;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\AbstractInvoiceDocumentUploadHandler;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class HttpInvoiceDocumentUploadHandler extends AbstractInvoiceDocumentUploadHandler implements LoggingInterface
{
    use LoggingTrait;

    protected const SUPPORTED_STRATEGY = MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_HTTP;

    private ProducerInterface $producer;

    public function __construct(
        ProducerInterface $producer,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository
    ) {
        $this->producer = $producer;

        parent::__construct($merchantSettingsRepository);
    }

    public function handle(
        OrderEntity $order,
        string $invoiceUuid,
        string $invoiceUrl,
        string $invoiceNumber,
        string $eventSource
    ): void {
        $message = new HttpInvoiceUploadRequest(
            $order->getMerchantId(),
            $order->getExternalCode(),
            $invoiceUuid,
            $invoiceUrl,
            $invoiceNumber,
            $eventSource
        );

        $data = json_encode($message->toArray());

        try {
            $this->producer->publish($data, 'http_invoice_upload');
        } catch (\Exception $exception) {
            $this->logSuppressedException($exception, 'Rabbit producer exception', ['data' => $data]);
        }
    }
}
