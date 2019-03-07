<?php

namespace App\Application\UseCase\GetMerchantDebtor;

class GetMerchantDebtorRequest
{
    private $merchantDebtorExternalId;

    private $merchantId;

    public function __construct(string $merchantDebtorExternalId, int $merchantId)
    {
        $this->merchantDebtorExternalId = $merchantDebtorExternalId;
        $this->merchantId = $merchantId;
    }

    public function getMerchantDebtorExternalId(): string
    {
        return $this->merchantDebtorExternalId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
