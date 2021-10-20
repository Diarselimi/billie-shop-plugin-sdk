<?php

namespace App\Application\UseCase\InitiateCheckoutSession;

use App\DomainModel\CheckoutSession\Country;
use App\DomainModel\CheckoutSession\Token;
use App\DomainModel\Merchant\PartnerIdentifier;

class InitiateCheckoutSession
{
    private Token $token;

    private Country $country;

    private ?int $merchantId = null;

    private ?string $debtorExternalId = null;

    private ?PartnerIdentifier $partnerIdentifier = null;

    private function __construct(string $token, string $countryCode)
    {
        $this->token = Token::fromHash($token);
        $this->country = new Country($countryCode);
    }

    public static function forKlarna(string $token, string $countryCode, string $klarnaMerchantId): self
    {
        $self = new self($token, $countryCode);
        $self->partnerIdentifier = PartnerIdentifier::create($klarnaMerchantId);

        return $self;
    }

    public static function forDirectIntegration(string $token, string $countryCode, int $merchantId, string $debtorExternalId): self
    {
        $self = new self($token, $countryCode);
        $self->merchantId = $merchantId;
        $self->debtorExternalId = $debtorExternalId;

        return $self;
    }

    public function token(): Token
    {
        return $this->token;
    }

    public function country(): Country
    {
        return $this->country;
    }

    public function isDirectIntegration(): bool
    {
        return null !== $this->merchantId;
    }

    public function merchantId(): ?int
    {
        return $this->merchantId;
    }

    public function debtorExternalId(): ?string
    {
        return $this->debtorExternalId;
    }

    public function partnerIdentifier(): ?PartnerIdentifier
    {
        return $this->partnerIdentifier;
    }
}
