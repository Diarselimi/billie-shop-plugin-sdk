<?php

namespace App\Application\UseCase\HttpInvoiceUpload;

use App\DomainModel\ArrayableInterface;

class HttpInvoiceUploadRequest implements ArrayableInterface
{
    private int $merchantId;

    private string $orderExternalCode;

    private string $invoiceUuid;

    private string $invoiceUrl;

    private string $invoiceNumber;

    private string $eventSource;

    public function __construct(
        int $merchantId,
        string $orderExternalCode,
        string $invoiceUuid,
        string $invoiceUrl,
        string $invoiceNumber,
        string $eventSource
    ) {
        $this->merchantId = $merchantId;
        $this->orderExternalCode = $orderExternalCode;
        $this->invoiceUuid = $invoiceUuid;
        $this->invoiceUrl = $invoiceUrl;
        $this->invoiceNumber = $invoiceNumber;
        $this->eventSource = $eventSource;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getOrderExternalCode(): string
    {
        return $this->orderExternalCode;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }

    public function getInvoiceUrl(): string
    {
        return $this->invoiceUrl;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getEventSource(): string
    {
        return $this->eventSource;
    }

    public function toArray(): array
    {
        return [
            'merchant_id' => $this->merchantId,
            'order_external_code' => $this->orderExternalCode,
            'invoice_uuid' => $this->invoiceUuid,
            'invoice_url' => $this->invoiceUrl,
            'invoice_number' => $this->getInvoiceNumber(),
            'event_source' => $this->eventSource,
        ];
    }
}
