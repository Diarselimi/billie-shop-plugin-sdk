<?php

namespace App\DomainModel\MerchantUser;

class AuthenticationServiceUserResponseDTO
{
    private $userId;

    private $userEmail;

    public function __construct(string $userId, string $userEmail)
    {
        $this->userId = $userId;
        $this->userEmail = $userEmail;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }
}
