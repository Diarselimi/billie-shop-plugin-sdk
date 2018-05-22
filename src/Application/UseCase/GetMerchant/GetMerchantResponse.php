<?php

namespace App\Application\UseCase\GetMerchant;

class GetMerchantResponse
{
    private $merchantData;

    public function __construct(array $merchantData)
    {
        $this->merchantData = $merchantData;
    }

    public function getMerchantData(): array
    {
        return $this->merchantData;
    }
}
