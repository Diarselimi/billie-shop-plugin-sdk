<?php

namespace App\Application\UseCase\HttpInvoiceUpload;

use App\DomainModel\ArrayableInterface;

class HttpInvoiceUploadRequest implements ArrayableInterface
{
    private $merchantId;

    private $orderExternalCode;

    private $invoiceUrl;

    private $invoiceNumber;

    private $event;

    public function __construct(
        int $merchantId,
        string $orderExternalCode,
        string $invoiceUrl,
        string $invoiceNumber,
        string $event
    ) {
        $this->merchantId = $merchantId;
        $this->orderExternalCode = $orderExternalCode;
        $this->invoiceUrl = $invoiceUrl;
        $this->invoiceNumber = $invoiceNumber;
        $this->event = $event;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getOrderExternalCode(): string
    {
        return $this->orderExternalCode;
    }

    public function getInvoiceUrl(): string
    {
        return $this->invoiceUrl;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function toArray(): array
    {
        return [
            'merchant_id' => $this->merchantId,
            'order_external_code' => $this->orderExternalCode,
            'invoice_url' => $this->invoiceUrl,
            'invoice_number' => $this->getInvoiceNumber(),
            'event' => $this->event,
        ];
    }
}
