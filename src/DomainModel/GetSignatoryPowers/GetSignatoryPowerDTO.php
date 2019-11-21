<?php

namespace App\DomainModel\GetSignatoryPowers;

class GetSignatoryPowerDTO
{
    private $uuid;

    private $firstName;

    private $lastName;

    private $additionalSignatoriesRequired;

    private $addressHouse;

    private $addressStreet;

    private $addressCity;

    private $addressPostalCode;

    private $addressCountry;

    private $automaticallyIdentifiedAsUser;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): GetSignatoryPowerDTO
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): GetSignatoryPowerDTO
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): GetSignatoryPowerDTO
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAdditionalSignatoriesRequired(): int
    {
        return $this->additionalSignatoriesRequired;
    }

    public function setAdditionalSignatoriesRequired(int $additionalSignatoriesRequired): GetSignatoryPowerDTO
    {
        $this->additionalSignatoriesRequired = $additionalSignatoriesRequired;

        return $this;
    }

    public function getAddressHouse(): ?string
    {
        return $this->addressHouse;
    }

    public function setAddressHouse(?string $addressHouse): GetSignatoryPowerDTO
    {
        $this->addressHouse = $addressHouse;

        return $this;
    }

    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(?string $addressStreet): GetSignatoryPowerDTO
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): GetSignatoryPowerDTO
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressPostalCode(): ?string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(?string $addressPostalCode): GetSignatoryPowerDTO
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): GetSignatoryPowerDTO
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function isAutomaticallyIdentifiedAsUser(): bool
    {
        return $this->automaticallyIdentifiedAsUser;
    }

    public function setAutomaticallyIdentifiedAsUser(bool $automaticallyIdentifiedAsUser): GetSignatoryPowerDTO
    {
        $this->automaticallyIdentifiedAsUser = $automaticallyIdentifiedAsUser;

        return $this;
    }
}
