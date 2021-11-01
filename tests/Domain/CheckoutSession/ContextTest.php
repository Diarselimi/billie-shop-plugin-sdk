<?php

namespace App\Tests\Domain\CheckoutSession;

use App\DomainModel\CheckoutSession\Context;
use App\DomainModel\CheckoutSession\ContextNotSupported;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    public function notSupportedCountries(): array
    {
        return [
            'BR' => ['BR'],
            'XK' => ['XK'],
        ];
    }

    /**
     * @test
     * @dataProvider notSupportedCountries
     */
    public function throwExceptionIfCountryIsNotSupported(string $country): void
    {
        self::expectException(ContextNotSupported::class);
        self::expectExceptionMessage("Country '$country' not supported");

        new Context($country, 'ANY');
    }

    public function notSupportedCurrencies(): array
    {
        return [
            'BRL' => ['BRL'],
            'RUB' => ['RUB'],
        ];
    }

    /**
     * @test
     * @dataProvider notSupportedCurrencies
     */
    public function throwExceptionIfCurrencyIsNotSupported(string $currency): void
    {
        self::expectException(ContextNotSupported::class);
        self::expectExceptionMessage("Currency '$currency' not supported in country 'DE'");

        new Context('DE', $currency);
    }
}
