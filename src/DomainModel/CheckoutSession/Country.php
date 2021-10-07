<?php

namespace App\DomainModel\CheckoutSession;

class Country
{
    private const SUPPORTED = ['DE'];

    private string $code;

    public function __construct(string $code)
    {
        if (!in_array($code, self::SUPPORTED)) {
            throw new CountryNotSupported($code);
        }

        $this->code = $code;
    }
}
