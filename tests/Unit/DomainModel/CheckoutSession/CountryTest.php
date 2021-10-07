<?php

namespace App\Tests\Unit\DomainModel\CheckoutSession;

use App\DomainModel\CheckoutSession\Country;
use App\DomainModel\CheckoutSession\CountryNotSupported;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    public function notSupportedCountryCodes(): array
    {
        return [
            'BR' => ['BR'],
            'XK' => ['XK'],
        ];
    }

    /**
     * @test
     * @dataProvider notSupportedCountryCodes
     */
    public function throwExceptionIfCountryIsNotSupported(string $countryCode): void
    {
        $this->expectException(CountryNotSupported::class);
        $this->expectExceptionMessage("Country not supported: '$countryCode'");

        new Country($countryCode);
    }
}
