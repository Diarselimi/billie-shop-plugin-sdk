<?php

declare(strict_types=1);

namespace App\DomainModel\Address;

use App\DomainModel\Address\Exception\InvalidCountryException;

class Address
{
    private const COUNTRY_REGEX = '/^[A-Z]{2}$/';

    private string $street;

    private ?string $houseNumber;

    private string $postalCode;

    private string $city;

    private string $country;

    public function __construct(string $street, string $houseNumber, string $postalCode, string $city, string $country)
    {
        $this->street = $street;
        $this->houseNumber = $houseNumber;
        $this->city = $city;
        if (preg_match(self::COUNTRY_REGEX, $country) === 0) {
            throw new InvalidCountryException();
        }

        $this->country = $country;
        $this->postalCode = $postalCode;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }
}
