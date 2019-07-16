<?php

namespace App\Application\UseCase\CreateOrderInvoice;

class CreateOrderInvoiceRequest
{
    private $orderUuid;

    private $fileId;

    private $invoiceNumber;

    public function __construct(string $orderUuid, int $fileId, string $invoiceNumber)
    {
        $this->orderUuid = $orderUuid;
        $this->fileId = $fileId;
        $this->invoiceNumber = $invoiceNumber;
    }

    public function getOrderUuid(): string
    {
        return $this->orderUuid;
    }

    public function getFileId(): int
    {
        return $this->fileId;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }
}
