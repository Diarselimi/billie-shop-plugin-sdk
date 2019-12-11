<?php

namespace App\DomainModel\MerchantUser;

class GetMerchantCredentialsDTO
{
    private $clientId;

    private $secret;

    public function __construct(string $clientId, string $secret)
    {
        $this->clientId = $clientId;
        $this->secret = $secret;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }
}
