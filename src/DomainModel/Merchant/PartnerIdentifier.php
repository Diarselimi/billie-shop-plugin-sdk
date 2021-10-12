<?php

declare(strict_types=1);

namespace App\DomainModel\Merchant;

class PartnerIdentifier
{
    private string $partnerIdentifier;

    private function __construct(string $partnerIdentifier)
    {
        $this->partnerIdentifier = $partnerIdentifier;
    }

    public static function create(string $merchantExternalId): self
    {
        return new self($merchantExternalId);
    }

    public function __toString(): string
    {
        return $this->partnerIdentifier;
    }
}
