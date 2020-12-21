<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateOrderInvoice;

class CreateOrderInvoiceRequest
{
    private string $orderUuid;

    private string $invoiceUuid;

    private string $invoiceNumber;

    private int $fileId;

    private string $fileUuid;

    public function __construct(
        string $orderUuid,
        string $invoiceUuid,
        string $invoiceNumber,
        string $fileUuid,
        int $fileId
    ) {
        $this->orderUuid = $orderUuid;
        $this->fileId = $fileId;
        $this->fileUuid = $fileUuid;
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceUuid = $invoiceUuid;
    }

    public function getOrderUuid(): string
    {
        return $this->orderUuid;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }

    /**
     * @deprecated
     */
    public function getFileId(): int
    {
        return $this->fileId;
    }

    public function getFileUuid(): string
    {
        return $this->fileUuid;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }
}
