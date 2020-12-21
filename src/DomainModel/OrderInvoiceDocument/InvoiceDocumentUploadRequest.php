<?php

declare(strict_types=1);

namespace App\DomainModel\OrderInvoiceDocument;

class InvoiceDocumentUploadRequest
{
    private int $orderId;

    private string $invoiceUuid;

    private string $invoiceUrl;

    private string $invoiceNumber;

    public function __construct(int $orderId, string $invoiceUuid, string $invoiceNumber, string $invoiceUrl)
    {
        $this->orderId = $orderId;
        $this->invoiceUuid = $invoiceUuid;
        $this->invoiceUrl = $invoiceUrl;
        $this->invoiceNumber = $invoiceNumber;
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

    /**
     * @deprecated
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }
}
