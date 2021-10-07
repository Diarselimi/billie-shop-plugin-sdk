<?php

namespace App\DomainModel\CheckoutSession;

class CountryNotSupported extends \InvalidArgumentException
{
    public function __construct(string $countryCode)
    {
        parent::__construct("Country not supported: '$countryCode'");
    }
}
