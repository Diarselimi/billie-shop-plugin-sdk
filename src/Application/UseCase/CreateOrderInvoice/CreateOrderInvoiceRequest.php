<?php

namespace App\Application\UseCase\CreateOrderInvoice;

class CreateOrderInvoiceRequest
{
    private $merchantId;

    private $orderExternalCode;

    private $fileId;

    private $invoiceNumber;

    public function __construct(int $merchantId, string $orderExternalCode, int $fileId, string $invoiceNumber)
    {
        $this->merchantId = $merchantId;
        $this->orderExternalCode = $orderExternalCode;
        $this->fileId = $fileId;
        $this->invoiceNumber = $invoiceNumber;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getOrderExternalCode(): string
    {
        return $this->orderExternalCode;
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
