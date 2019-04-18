<?php

namespace App\DomainModel\OrderInvoice;

interface InvoiceUploadHandlerInterface
{
    public const EVENT_MIGRATION = 'order.migration';

    public const EVENT_UPDATE = 'order.update';

    public const EVENT_SHIPMENT = 'order.shipment';

    public function handleInvoice(
        string $orderExternalCode,
        int $merchantId,
        string $invoiceNumber,
        string $invoiceUrl,
        string $event
    ): void;

    public function supports(
        string $orderExternalCode,
        int $merchantId,
        string $invoiceNumber,
        string $invoiceUrl
    ): bool;
}
