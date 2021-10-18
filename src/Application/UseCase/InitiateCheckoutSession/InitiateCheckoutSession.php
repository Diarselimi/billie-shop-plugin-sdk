<?php

namespace App\Application\UseCase\InitiateCheckoutSession;

use App\DomainModel\CheckoutSession\Country;
use App\DomainModel\CheckoutSession\Token;

class InitiateCheckoutSession
{
    private Token $token;

    private Country $country;

    private int $merchantId;

    private ?string $externalReference;

    public function __construct(string $tokenSeed, string $countryCode, int $merchantId, ?string $externalReference)
    {
        $this->token = Token::fromSeed($tokenSeed);
        $this->country = new Country($countryCode);
        $this->merchantId = $merchantId;
        $this->externalReference = $externalReference;
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

    public function externalReference(): ?string
    {
        return $this->externalReference;
    }
}
