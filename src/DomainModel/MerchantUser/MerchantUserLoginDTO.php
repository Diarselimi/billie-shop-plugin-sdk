<?php

namespace App\DomainModel\MerchantUser;

class MerchantUserLoginDTO
{
    private $userUuid;

    private $email;

    private $accessToken;

    public function __construct(string $userUuid, string $email, string $accessToken)
    {
        $this->userUuid = $userUuid;
        $this->email = $email;
        $this->accessToken = $accessToken;
    }

    public function getUserUuid(): string
    {
        return $this->userUuid;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}
