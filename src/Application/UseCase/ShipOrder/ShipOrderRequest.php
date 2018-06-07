<?php

namespace App\Application\UseCase\ShipOrder;

class ShipOrderRequest
{
    private $externalCode;
    private $customerId;
    private $invoiceNumber;

    public function __construct(string $externalCode, int $customerId, string $invoiceNumber)
    {
        $this->externalCode = $externalCode;
        $this->customerId = $customerId;
        $this->invoiceNumber = $invoiceNumber;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }
}
