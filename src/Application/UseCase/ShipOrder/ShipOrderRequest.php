<?php

namespace App\Application\UseCase\ShipOrder;

class ShipOrderRequest
{
    private $externalCode;
    private $customerId;

    public function __construct(string $externalCode, int $customerId)
    {
        $this->externalCode = $externalCode;
        $this->customerId = $customerId;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }
}
