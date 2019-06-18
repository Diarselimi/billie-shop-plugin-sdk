<?php

namespace App\DomainModel\MerchantUser;

class AuthenticationServiceTokenResponseDTO
{
    private $tokenType;

    private $expiresIn;

    private $accessToken;

    private $refreshToken;

    public function __construct(string $tokenType, int $expiresIn, string $accessToken, string $refreshToken)
    {
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
