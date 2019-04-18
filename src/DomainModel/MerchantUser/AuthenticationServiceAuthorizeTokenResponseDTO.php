<?php

namespace App\DomainModel\MerchantUser;

class AuthenticationServiceAuthorizeTokenResponseDTO
{
    private $clientId;

    private $userId;

    public function __construct(string $clientId, string $userId = null)
    {
        $this->clientId = $clientId;
        $this->userId = $userId;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getUserId(): ? string
    {
        return $this->userId;
    }
}
