<?php

declare(strict_types=1);

namespace App\DomainModel\PasswordResetRequest;

class RequestPasswordResetDTO
{
    private $userUuid;

    private $token;

    public function __construct(string $userUuid, string $token)
    {
        $this->userUuid = $userUuid;
        $this->token = $token;
    }

    public function getUserUuid(): string
    {
        return $this->userUuid;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
