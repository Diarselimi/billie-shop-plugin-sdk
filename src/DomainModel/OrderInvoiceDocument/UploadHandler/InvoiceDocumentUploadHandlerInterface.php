<?php

declare(strict_types=1);

namespace App\DomainModel\OrderInvoiceDocument\UploadHandler;

use App\DomainModel\Order\OrderEntity;

interface InvoiceDocumentUploadHandlerInterface
{
    public const EVENT_SOURCE_UPDATE = 'order.update';

    public const EVENT_SOURCE_SHIPMENT = 'order.shipment';

    public function handle(
        OrderEntity $order,
        string $invoiceUuid,
        string $invoiceUrl,
        string $invoiceNumber,
        string $eventSource
    ): void;

    public function supports(int $merchantId): bool;
}
