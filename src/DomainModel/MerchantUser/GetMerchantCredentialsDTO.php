<?php

namespace App\DomainModel\MerchantUser;

class GetMerchantCredentialsDTO
{
    private $clientId;

    private $secret;

    public function __construct(?string $clientId = null, ?string $secret = null)
    {
        $this->clientId = $clientId;
        $this->secret = $secret;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function toArray()
    {
        return [
            'client_id' => $this->getClientId(),
            'secret' => $this->getSecret(),
        ];
    }
}
