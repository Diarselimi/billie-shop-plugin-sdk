<?php

declare(strict_types=1);

namespace App\DomainModel\OrderInvoiceDocument;

class InvoiceDocumentUpload
{
    public const TYPE_INVOICE = 'invoice';

    private int $orderId;

    private string $invoiceUuid;

    private string $invoiceNumber;

    private string $fileUuid;

    private string $type;

    private int $fileId;

    public function __construct(int $orderId, string $invoiceUuid, string $invoiceNumber, string $fileUuid, int $fileId)
    {
        $this->orderId = $orderId;
        $this->invoiceUuid = $invoiceUuid;
        $this->invoiceNumber = $invoiceNumber;
        $this->fileUuid = $fileUuid;
        $this->fileId = $fileId;
        $this->type = self::TYPE_INVOICE;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }

    public function getFileUuid(): string
    {
        return $this->fileUuid;
    }

    /**
     * @deprecated
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    /**
     * @deprecated use getFileUuid()
     */
    public function getFileId(): int
    {
        return $this->fileId;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
