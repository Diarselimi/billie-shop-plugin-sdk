<?php

namespace App\Application\UseCase\InitiateCheckoutSession;

use App\DomainModel\CheckoutSession\Context;
use App\DomainModel\CheckoutSession\Token;
use App\DomainModel\Merchant\PartnerIdentifier;

class InitiateCheckoutSession
{
    private Token $token;

    private Context $context;

    private ?int $merchantId = null;

    private ?string $debtorExternalId = null;

    private ?PartnerIdentifier $partnerIdentifier = null;

    private function __construct(string $token, string $country, string $currency)
    {
        $this->token = Token::fromHash($token);
        $this->context = new Context($country, $currency);
    }

    public static function forKlarna(string $token, string $country, string $currency, string $klarnaMerchantId): self
    {
        $self = new self($token, $country, $currency);
        $self->partnerIdentifier = PartnerIdentifier::create($klarnaMerchantId);

        return $self;
    }

    public static function forDirectIntegration(string $token, int $merchantId, string $debtorExternalId): self
    {
        $self = new self($token, 'DE', 'EUR');
        $self->merchantId = $merchantId;
        $self->debtorExternalId = $debtorExternalId;

        return $self;
    }

    public function isDirectIntegration(): bool
    {
        return null !== $this->merchantId;
    }

    public function token(): Token
    {
        return $this->token;
    }

    public function context(): Context
    {
        return $this->context;
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
