<?php

namespace App\Application\UseCase\InitiateCheckoutSession;

use App\DomainModel\CheckoutSession\Country;
use App\DomainModel\CheckoutSession\Token;

class InitiateCheckoutSession
{
    private Token $token;

    private Country $country;

    private int $merchantId;

    public function __construct(string $tokenSeed, string $countryCode, int $merchantId)
    {
        $this->token = Token::fromSeed($tokenSeed);
        $this->country = new Country($countryCode);
        $this->merchantId = $merchantId;
    }

    public function token(): Token
    {
        return $this->token;
    }

    public function country(): Country
    {
        return $this->country;
    }

    public function merchantId(): int
    {
        return $this->merchantId;
    }
}
