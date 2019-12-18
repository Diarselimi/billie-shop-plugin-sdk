<?php

namespace App\Application\UseCase\MerchantStartIntegration;

class MerchantStartIntegrationRequest
{
    private $merchantId;

    public function __construct(int $merchantId)
    {
        $this->merchantId = $merchantId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
