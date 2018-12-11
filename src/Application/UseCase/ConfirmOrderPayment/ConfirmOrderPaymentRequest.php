<?php

namespace App\Application\UseCase\ConfirmOrderPayment;

class ConfirmOrderPaymentRequest
{
    private $externalCode;

    private $customerId;

    private $amount;

    public function __construct(string $externalCode, int $customerId, float $amount)
    {
        $this->externalCode = $externalCode;
        $this->customerId = $customerId;
        $this->amount = $amount;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}
