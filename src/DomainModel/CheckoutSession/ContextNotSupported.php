<?php

namespace App\DomainModel\CheckoutSession;

class ContextNotSupported extends \InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function countryNotSupported(Context $context): self
    {
        return new self("Country '{$context->country()}' not supported");
    }

    public static function currencyNotSupportedInCountry(Context $context): self
    {
        return new self("Currency '{$context->currency()}' not supported in country '{$context->country()}'");
    }
}
