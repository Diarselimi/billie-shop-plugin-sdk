<?php

namespace App\Application\UseCase\GetMerchantDebtor;

use App\DomainModel\ArrayableInterface;

class GetMerchantDebtorResponse implements ArrayableInterface
{
    private $merchantDebtorData;

    public function __construct(array $merchantDebtorData)
    {
        $this->merchantDebtorData = $merchantDebtorData;
    }

    public function getMerchantDebtorData(): array
    {
        return $this->merchantDebtorData;
    }

    public function toArray(): array
    {
        return $this->merchantDebtorData;
    }
}
