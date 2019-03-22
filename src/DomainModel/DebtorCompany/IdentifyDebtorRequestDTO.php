<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\ArrayableInterface;

class IdentifyDebtorRequestDTO implements ArrayableInterface
{
    private $companyId;

    private $name;

    private $houseNumber;

    private $street;

    private $postalCode;

    private $city;

    private $country;

    private $taxId;

    private $taxNumber;

    private $registrationNumber;

    private $registrationCourt;

    private $legalForm;

    private $firstName;

    private $lastName;

    private $isExperimental;

    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    public function setCompanyId(int $companyId): IdentifyDebtorRequestDTO
    {
        $this->companyId = $companyId;

        return $this;
    }

    public function getName(): ? string
    {
        return $this->name;
    }

    public function setName(?string $name): IdentifyDebtorRequestDTO
    {
        $this->name = $name;

        return $this;
    }

    public function getHouseNumber(): ? string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(? string $houseNumber): IdentifyDebtorRequestDTO
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function getStreet(): ? string
    {
        return $this->street;
    }

    public function setStreet(?string $street): IdentifyDebtorRequestDTO
    {
        $this->street = $street;

        return $this;
    }

    public function getPostalCode(): ? string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): IdentifyDebtorRequestDTO
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ? string
    {
        return $this->city;
    }

    public function setCity(?string $city): IdentifyDebtorRequestDTO
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ? string
    {
        return $this->country;
    }

    public function setCountry(?string $country): IdentifyDebtorRequestDTO
    {
        $this->country = $country;

        return $this;
    }

    public function getTaxId(): ? string
    {
        return $this->taxId;
    }

    public function setTaxId(?string $taxId): IdentifyDebtorRequestDTO
    {
        $this->taxId = $taxId;

        return $this;
    }

    public function getTaxNumber(): ? string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(?string $taxNumber): IdentifyDebtorRequestDTO
    {
        $this->taxNumber = $taxNumber;

        return $this;
    }

    public function getRegistrationNumber(): ? string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): IdentifyDebtorRequestDTO
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getRegistrationCourt(): ? string
    {
        return $this->registrationCourt;
    }

    public function setRegistrationCourt(?string $registrationCourt): IdentifyDebtorRequestDTO
    {
        $this->registrationCourt = $registrationCourt;

        return $this;
    }

    public function getLegalForm(): ? string
    {
        return $this->legalForm;
    }

    public function setLegalForm(?string $legalForm): IdentifyDebtorRequestDTO
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    public function getFirstName(): ? string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): IdentifyDebtorRequestDTO
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ? string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): IdentifyDebtorRequestDTO
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function isExperimental(): bool
    {
        return $this->isExperimental;
    }

    public function setIsExperimental(bool $isExperimental): IdentifyDebtorRequestDTO
    {
        $this->isExperimental = $isExperimental;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'company_id' => $this->getCompanyId(),
            'name' => $this->getName(),
            'address_house' => $this->getHouseNumber(),
            'address_street' => $this->getStreet(),
            'address_postal_code' => $this->getPostalCode(),
            'address_city' => $this->getCity(),
            'address_country' => $this->getCountry(),
            'tax_id' => $this->getTaxId(),
            'tax_number' => $this->getTaxNumber(),
            'registration_number' => $this->getRegistrationNumber(),
            'registration_court' => $this->getRegistrationCourt(),
            'legal_form' => $this->getLegalForm(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'is_experimental' => $this->isExperimental(),
        ];
    }
}
