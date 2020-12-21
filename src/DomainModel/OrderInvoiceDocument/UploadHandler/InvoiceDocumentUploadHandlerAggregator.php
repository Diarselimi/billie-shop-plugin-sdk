<?php

declare(strict_types=1);

namespace App\DomainModel\OrderInvoiceDocument\UploadHandler;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentUploadException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class InvoiceDocumentUploadHandlerAggregator implements LoggingInterface, InvoiceDocumentUploadHandlerInterface
{
    use LoggingTrait;

    private array $uploadHandlers;

    /**
     * @param InvoiceDocumentUploadHandlerInterface[] $invoiceUploadHandlers
     */
    public function __construct(array $invoiceUploadHandlers)
    {
        $this->uploadHandlers = $invoiceUploadHandlers;
    }

    public function handle(
        OrderEntity $order,
        string $invoiceUuid,
        string $invoiceUrl,
        string $invoiceNumber,
        string $eventSource
    ): void {
        $logData = [LoggingInterface::KEY_NUMBER => $order->getInvoiceNumber()];

        foreach ($this->uploadHandlers as $name => $handler) {
            if ($handler->supports($order->getMerchantId())) {
                $logData[LoggingInterface::KEY_NAME] = $name;
                $this->logInfo('Handling URL for invoice {number} using {name} handler', $logData);

                $handler->handle($order, $invoiceUuid, $invoiceUrl, $invoiceNumber, $eventSource);

                return;
            }
        }

        $this->logInfo('No supported handler for {number} found', $logData);

        throw new InvoiceDocumentUploadException('No supported handler found');
    }

    public function supports(int $merchantId): bool
    {
        return true;
    }
}
