<?php

namespace App\DomainModel\CheckoutSession;

class CheckoutSession
{
    private Token $token;

    private Country $country;

    private int $merchantId;

    private bool $isActive = true;

    public function __construct(Token $token, Country $country, int $merchantId)
    {
        $this->token = $token;
        $this->country = $country;
        $this->merchantId = $merchantId;
    }

    public function token(): Token
    {
        return $this->token;
    }

    public function merchantId(): int
    {
        return $this->merchantId;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
