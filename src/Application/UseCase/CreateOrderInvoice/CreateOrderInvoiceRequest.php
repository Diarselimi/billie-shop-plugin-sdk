<?php

namespace App\Application\UseCase\CreateOrderInvoice;

use App\Application\UseCase\AbstractOrderRequest;

class CreateOrderInvoiceRequest extends AbstractOrderRequest
{
    private $fileId;

    private $invoiceNumber;

    public function __construct(string $orderId, int $merchantId, int $fileId, string $invoiceNumber)
    {
        parent::__construct($orderId, $merchantId);

        $this->fileId = $fileId;
        $this->invoiceNumber = $invoiceNumber;
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
