<?php

namespace App\DomainModel\SignatoryPower;

class SignatoryPowerDTO
{
    private $uuid;

    private $companyUuid;

    private $firstName;

    private $lastName;

    private $email;

    private $addressHouse;

    private $addressStreet;

    private $addressCity;

    private $addressPostalCode;

    private $addressCountry;

    private $additionalSignatoriesRequired;

    private $isIdentityVerified;

    private $identityVerificationUrl;

    private $automaticallyIdentifiedAsUser;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): SignatoryPowerDTO
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): SignatoryPowerDTO
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): SignatoryPowerDTO
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): SignatoryPowerDTO
    {
        $this->email = $email;

        return $this;
    }

    public function getAdditionalSignatoriesRequired(): int
    {
        return $this->additionalSignatoriesRequired;
    }

    public function setAdditionalSignatoriesRequired(int $additionalSignatoriesRequired): SignatoryPowerDTO
    {
        $this->additionalSignatoriesRequired = $additionalSignatoriesRequired;

        return $this;
    }

    public function getAddressHouse(): ?string
    {
        return $this->addressHouse;
    }

    public function setAddressHouse(?string $addressHouse): SignatoryPowerDTO
    {
        $this->addressHouse = $addressHouse;

        return $this;
    }

    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(?string $addressStreet): SignatoryPowerDTO
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): SignatoryPowerDTO
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressPostalCode(): ?string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(?string $addressPostalCode): SignatoryPowerDTO
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): SignatoryPowerDTO
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function isAutomaticallyIdentifiedAsUser(): bool
    {
        return $this->automaticallyIdentifiedAsUser;
    }

    public function setAutomaticallyIdentifiedAsUser(bool $automaticallyIdentifiedAsUser): SignatoryPowerDTO
    {
        $this->automaticallyIdentifiedAsUser = $automaticallyIdentifiedAsUser;

        return $this;
    }

    public function getCompanyUuid(): ?string
    {
        return $this->companyUuid;
    }

    public function setCompanyUuid(?string $companyUuid): SignatoryPowerDTO
    {
        $this->companyUuid = $companyUuid;

        return $this;
    }

    public function isIdentityVerified(): bool
    {
        return $this->isIdentityVerified;
    }

    public function setIsIdentityVerified(bool $isIdentityVerified): SignatoryPowerDTO
    {
        $this->isIdentityVerified = $isIdentityVerified;

        return $this;
    }

    public function getIdentityVerificationUrl(): ?string
    {
        return $this->identityVerificationUrl;
    }

    public function setIdentityVerificationUrl(?string $identityVerificationUrl): SignatoryPowerDTO
    {
        $this->identityVerificationUrl = $identityVerificationUrl;

        return $this;
    }
}
