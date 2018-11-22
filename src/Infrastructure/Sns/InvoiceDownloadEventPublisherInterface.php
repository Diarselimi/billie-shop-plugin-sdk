<?php

namespace App\Infrastructure\Sns;

interface InvoiceDownloadEventPublisherInterface
{
    public function publish(int $orderId, int $merchantId, string $invoiceNumber, string $basePath = '/'): bool;
}
