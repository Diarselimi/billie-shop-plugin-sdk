<?php

namespace App\Application\UseCase\CreateOrderInvoice;

class CreateOrderInvoiceRequest
{
    private $merchantId;

    private $orderId;

    private $fileId;

    private $invoiceNumber;

    public function __construct(int $merchantId, string $orderId, int $fileId, string $invoiceNumber)
    {
        $this->merchantId = $merchantId;
        $this->orderId = $orderId;
        $this->fileId = $fileId;
        $this->invoiceNumber = $invoiceNumber;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
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
