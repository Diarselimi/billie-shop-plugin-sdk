<?php

namespace App\Application\UseCase\InitiateCheckoutSession;

class MerchantNotFound extends \RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function forPartner(string $klarnaMerchantId): self
    {
        return new self("Could not found klarna merchant id '$klarnaMerchantId'");
    }

    public static function forMerchant(int $merchantId): self
    {
        return new self("Could not found merchant id '$merchantId'");
    }
}
