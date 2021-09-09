<?php

namespace App\DomainModel\OrderInvoiceDocument\DomainEvent;

use Ozean12\AmqpPackBundle\DomainEvent;

class HttpUploadInvoiceDomainEvent extends DomainEvent
{
    private int $merchantId;

    private string $orderExternalCode;

    private ?string $invoiceUuid;

    private string $invoiceUrl;

    private string $invoiceNumber;

    private string $event;

    private ?string $eventSource;

    public function __construct(
        int $merchantId,
        string $orderExternalCode,
        ?string $invoiceUuid,
        string $invoiceUrl,
        string $invoiceNumber,
        string $event,
        ?string $eventSource
    ) {
        $this->merchantId = $merchantId;
        $this->orderExternalCode = $orderExternalCode;
        $this->invoiceUuid = $invoiceUuid;
        $this->invoiceUrl = $invoiceUrl;
        $this->invoiceNumber = $invoiceNumber;
        $this->event = $event;
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

    public function getInvoiceUuid(): ?string
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

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getEventSource(): ?string
    {
        return $this->eventSource;
    }
}
