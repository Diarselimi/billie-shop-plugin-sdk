<?php

namespace App\Application\UseCase\IdentifyAndScoreDebtor;

class IdentifyAndScoreDebtorRequest
{
    private $merchantId;

    private $useExperimentalDebtorIdentification;

    private $doScoring;

    private $name;

    private $addressHouse;

    private $addressStreet;

    private $addressPostalCode;

    private $addressCity;

    private $addressCountry;

    private $taxId;

    private $taxNumber;

    private $registrationNumber;

    private $registrationCourt;

    private $legalForm;

    private $firstName;

    private $lastName;

    private $limit;

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): IdentifyAndScoreDebtorRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function useExperimentalDebtorIdentification(): bool
    {
        return $this->useExperimentalDebtorIdentification;
    }

    public function setUseExperimentalDebtorIdentification(bool $useExperimentalDebtorIdentification): IdentifyAndScoreDebtorRequest
    {
        $this->useExperimentalDebtorIdentification = $useExperimentalDebtorIdentification;

        return $this;
    }

    public function isDoScoring(): bool
    {
        return $this->doScoring;
    }

    public function setDoScoring(bool $doScoring): IdentifyAndScoreDebtorRequest
    {
        $this->doScoring = $doScoring;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): IdentifyAndScoreDebtorRequest
    {
        $this->name = $name;

        return $this;
    }

    public function getAddressHouse(): ?string
    {
        return $this->addressHouse;
    }

    public function setAddressHouse(?string $addressHouse): IdentifyAndScoreDebtorRequest
    {
        $this->addressHouse = $addressHouse;

        return $this;
    }

    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(?string $addressStreet): IdentifyAndScoreDebtorRequest
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressPostalCode(): ?string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(?string $addressPostalCode): IdentifyAndScoreDebtorRequest
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): IdentifyAndScoreDebtorRequest
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): IdentifyAndScoreDebtorRequest
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function getTaxId(): ?string
    {
        return $this->taxId;
    }

    public function setTaxId(?string $taxId): IdentifyAndScoreDebtorRequest
    {
        $this->taxId = $taxId;

        return $this;
    }

    public function getTaxNumber(): ?string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(?string $taxNumber): IdentifyAndScoreDebtorRequest
    {
        $this->taxNumber = $taxNumber;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): IdentifyAndScoreDebtorRequest
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getRegistrationCourt(): ?string
    {
        return $this->registrationCourt;
    }

    public function setRegistrationCourt(?string $registrationCourt): IdentifyAndScoreDebtorRequest
    {
        $this->registrationCourt = $registrationCourt;

        return $this;
    }

    public function getLegalForm(): ?string
    {
        return $this->legalForm;
    }

    public function setLegalForm(?string $legalForm): IdentifyAndScoreDebtorRequest
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): IdentifyAndScoreDebtorRequest
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): IdentifyAndScoreDebtorRequest
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLimit(): ? float
    {
        return $this->limit;
    }

    public function setLimit(?float $limit): IdentifyAndScoreDebtorRequest
    {
        $this->limit = $limit;

        return $this;
    }
}
