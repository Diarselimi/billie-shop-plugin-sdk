<?php

namespace App\DomainModel\DebtorExternalData;

use App\DomainModel\AbstractEntity;

class DebtorExternalDataEntity extends AbstractEntity
{
    private $name;
    private $taxId;
    private $taxNumber;
    private $registrationNumber;
    private $registrationCourt;
    private $industrySector;
    private $subindustrySector;
    private $employees_number;
    private $legalForm;
    private $establishedCustomer;
    private $addressId;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): DebtorExternalDataEntity
    {
        $this->name = $name;

        return $this;
    }

    public function getTaxId():? string
    {
        return $this->taxId;
    }

    public function setTaxId(?string $taxId): DebtorExternalDataEntity
    {
        $this->taxId = $taxId;

        return $this;
    }

    public function getTaxNumber():? string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(?string $taxNumber): DebtorExternalDataEntity
    {
        $this->taxNumber = $taxNumber;

        return $this;
    }

    public function getRegistrationNumber():? string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): DebtorExternalDataEntity
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getRegistrationCourt():? string
    {
        return $this->registrationCourt;
    }

    public function setRegistrationCourt(?string $registrationCourt): DebtorExternalDataEntity
    {
        $this->registrationCourt = $registrationCourt;

        return $this;
    }

    public function getIndustrySector(): string
    {
        return $this->industrySector;
    }

    public function setIndustrySector(string $industrySector): DebtorExternalDataEntity
    {
        $this->industrySector = $industrySector;

        return $this;
    }

    public function getSubindustrySector(): string
    {
        return $this->subindustrySector;
    }

    public function setSubindustrySector(string $subindustrySector): DebtorExternalDataEntity
    {
        $this->subindustrySector = $subindustrySector;

        return $this;
    }

    public function getEmployeesNumber():? string
    {
        return $this->employees_number;
    }

    public function setEmployeesNumber(? string $employees_number): DebtorExternalDataEntity
    {
        $this->employees_number = $employees_number;

        return $this;
    }

    public function getLegalForm(): string
    {
        return $this->legalForm;
    }

    public function setLegalForm(string $legalForm): DebtorExternalDataEntity
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    public function isEstablishedCustomer(): bool
    {
        return $this->establishedCustomer;
    }

    public function setEstablishedCustomer(bool $establishedCustomer): DebtorExternalDataEntity
    {
        $this->establishedCustomer = $establishedCustomer;

        return $this;
    }

    public function getAddressId(): int
    {
        return $this->addressId;
    }

    public function setAddressId(int $addressId): DebtorExternalDataEntity
    {
        $this->addressId = $addressId;

        return $this;
    }
}
