<?php

namespace App\Application\UseCase\GetMerchant;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\Merchant\MerchantEntity;

class GetMerchantResponse implements ArrayableInterface
{
    private $merchant;

    public function __construct(MerchantEntity $merchant)
    {
        $this->merchant = $merchant;
    }

    public function getMerchant(): MerchantEntity
    {
        return $this->merchant;
    }

    public function toArray(): array
    {
        return $this->merchant->toArray();
    }
}
