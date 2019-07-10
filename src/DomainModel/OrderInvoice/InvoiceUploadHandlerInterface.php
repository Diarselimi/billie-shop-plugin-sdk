<?php

namespace App\DomainModel\OrderInvoice;

use App\DomainModel\Order\OrderEntity;

interface InvoiceUploadHandlerInterface
{
    public const EVENT_MIGRATION = 'order.migration';

    public const EVENT_UPDATE = 'order.update';

    public const EVENT_SHIPMENT = 'order.shipment';

    public function handleInvoice(OrderEntity $order, string $event): void;

    public function supports(int $merchantId): bool;
}
