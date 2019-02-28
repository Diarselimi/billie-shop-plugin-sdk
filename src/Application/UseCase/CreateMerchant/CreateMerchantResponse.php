<?php

namespace App\Application\UseCase\CreateMerchant;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\Merchant\MerchantEntity;

class CreateMerchantResponse implements ArrayableInterface
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
