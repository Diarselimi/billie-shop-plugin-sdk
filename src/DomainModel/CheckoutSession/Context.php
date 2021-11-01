<?php

namespace App\DomainModel\CheckoutSession;

class Context
{
    private const SUPPORTED_CURRENCIES_PER_COUNTRY = [
        'DE' => ['EUR'],
    ];

    private string $country;

    private string $currency;

    public function __construct(string $country, string $currency)
    {
        $this->country = $country;
        $this->currency = $currency;

        $this->assertItIsSupported();
    }

    public function country(): string
    {
        return $this->country;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    private function assertItIsSupported(): void
    {
        $supportedCurrencies = self::SUPPORTED_CURRENCIES_PER_COUNTRY[$this->country] ?? null;

        if (null === $supportedCurrencies) {
            throw ContextNotSupported::countryNotSupported($this);
        }

        if (!in_array($this->currency, $supportedCurrencies)) {
            throw ContextNotSupported::currencyNotSupportedInCountry($this);
        }
    }
}
