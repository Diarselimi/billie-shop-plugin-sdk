<?php

namespace App\Infrastructure\Sns;

interface InvoiceDownloadEventPublisherInterface
{
    public function publish(string $orderExternalCode, int $merchantId, string $invoiceNumber, string $basePath = '/'): bool;
}
