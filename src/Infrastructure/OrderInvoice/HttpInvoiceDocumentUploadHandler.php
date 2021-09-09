<?php

namespace App\Infrastructure\OrderInvoice;

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoiceDocument\DomainEvent\HttpUploadInvoiceDomainEvent;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\AbstractInvoiceDocumentUploadHandler;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class HttpInvoiceDocumentUploadHandler extends AbstractInvoiceDocumentUploadHandler implements LoggingInterface
{
    use LoggingTrait;

    protected const SUPPORTED_STRATEGY = MerchantSettingsEntity::INVOICE_HANDLING_STRATEGY_HTTP;

    private MessageBusInterface $bus;

    public function __construct(
        MessageBusInterface $bus,
        MerchantSettingsRepositoryInterface $merchantSettingsRepository
    ) {
        $this->bus = $bus;

        parent::__construct($merchantSettingsRepository);
    }

    public function handle(
        OrderEntity $order,
        string $invoiceUuid,
        string $invoiceUrl,
        string $invoiceNumber,
        string $eventSource
    ): void {
        try {
            $message = new HttpUploadInvoiceDomainEvent(
                $order->getMerchantId(),
                $order->getExternalCode(),
                $invoiceUuid,
                $invoiceUrl,
                $invoiceNumber,
                '',
                $eventSource
            );
            $this->bus->dispatch($message);
        } catch (\Exception $exception) {
            $this->logSuppressedException(
                $exception,
                'Rabbit producer exception',
                [
                    'data' => [
                        'merchant_id' => $order->getMerchantId(),
                        'external_code' => $order->getExternalCode(),
                        'invoice_uuid' => $invoiceUuid,
                        'invoice_url' => $invoiceUrl,
                        'invoice_number' => $invoiceNumber,
                        'event_source' => $eventSource,
                    ],
                ]
            );
        }
    }
}
