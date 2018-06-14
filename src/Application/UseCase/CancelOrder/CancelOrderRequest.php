<?php

namespace App\Application\UseCase\CancelOrder;

class CancelOrderRequest
{
    private $externalCode;
    private $merchantId;

    public function __construct(string $externalCode, int $merchantId)
    {
        $this->externalCode = $externalCode;
        $this->merchantId = $merchantId;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
