<?php

namespace App\Application\UseCase\GetOrder;

class GetOrderRequest
{
    private $externalCode;

    public function __construct(string $externalCode)
    {
        $this->externalCode = $externalCode;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }
}
