<?php

namespace App\Application\UseCase\GetMerchant;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantUser\GetMerchantCredentialsDTO;

class GetMerchantResponse implements ArrayableInterface
{
    private $merchant;

    private $credentialsDTO;

    public function __construct(MerchantEntity $merchant, ?GetMerchantCredentialsDTO $credentialsDTO)
    {
        $this->merchant = $merchant;
        $this->credentialsDTO = $credentialsDTO;
    }

    public function getMerchant(): MerchantEntity
    {
        return $this->merchant;
    }

    public function toArray(): array
    {
        return array_merge(
            $this->merchant->toArray(),
            [
                'credentials' => $this->credentialsDTO ? $this->credentialsDTO->toArray() : null,
            ]
        );
    }
}
