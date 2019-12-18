<?php

namespace App\DomainModel\Sandbox;

use App\DomainModel\Merchant\MerchantEntity;

class SandboxMerchantDTO
{
    private $merchant;

    private $oauthClientSecret;

    public function __construct(MerchantEntity $merchant, string $oauthClientSecret)
    {
        $this->merchant = $merchant;
        $this->oauthClientSecret = $oauthClientSecret;
    }

    public function getMerchant(): MerchantEntity
    {
        return $this->merchant;
    }

    public function getOauthClientSecret(): string
    {
        return $this->oauthClientSecret;
    }
}
