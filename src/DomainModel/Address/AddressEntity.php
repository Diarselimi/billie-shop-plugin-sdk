<?php

namespace App\DomainModel\Address;

use App\DomainModel\AbstractEntity;

class AddressEntity extends AbstractEntity
{
    private $country;
    private $city;
    private $postalCode;
    private $street;
    private $houseNumber;
    private $addition;
    private $comment;

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): AddressEntity
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): AddressEntity
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): AddressEntity
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): AddressEntity
    {
        $this->street = $street;

        return $this;
    }

    public function getHouseNumber(): string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(string $houseNumber): AddressEntity
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function getAddition():? string
    {
        return $this->addition;
    }

    public function setAddition(?string $addition): AddressEntity
    {
        $this->addition = $addition;

        return $this;
    }

    public function getComment():? string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): AddressEntity
    {
        $this->comment = $comment;

        return $this;
    }
}
