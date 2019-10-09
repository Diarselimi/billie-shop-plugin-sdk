<?php

namespace App\DomainModel\MerchantUser;

class AuthenticationServiceAuthorizeTokenResponseDTO
{
    private $clientId;

    private $userId;

    private $email;

    public function __construct(string $clientId, string $userId = null, string $email = null)
    {
        $this->clientId = $clientId;
        $this->userId = $userId;
        $this->email = $email;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getUserId(): ? string
    {
        return $this->userId;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
