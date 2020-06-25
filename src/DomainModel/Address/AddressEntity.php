<?php

declare(strict_types=1);

namespace App\DomainModel\Address;

use App\DomainModel\ArrayableInterface;
use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class AddressEntity extends AbstractTimestampableEntity implements ArrayableInterface
{
    private $uuid;

    private $country;

    private $city;

    private $postalCode;

    private $street;

    private $houseNumber;

    private $addition;

    public function getUuid(): ? string
    {
        return $this->uuid;
    }

    public function setUuid(? string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?string $houseNumber): self
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function getAddition(): ?string
    {
        return $this->addition;
    }

    public function setAddition(?string $addition): self
    {
        $this->addition = $addition;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'house_number' => $this->houseNumber,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }
}
