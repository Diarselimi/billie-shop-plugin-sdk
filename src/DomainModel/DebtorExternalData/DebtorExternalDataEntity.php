<?php

namespace App\DomainModel\DebtorExternalData;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class DebtorExternalDataEntity extends AbstractTimestampableEntity
{
    public const LEGAL_FORMS_FOR_SOLE_TRADERS = [
        '6022', '2001, 2018, 2022', '2001', '2018', '2022', '4001', '4022', '3001',
    ];

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

    private $merchantExternalId;

    private $addressId;

    private $billingAddressId;

    private $dataHash;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): DebtorExternalDataEntity
    {
        $this->name = $name;

        return $this;
    }

    public function getTaxId(): ? string
    {
        return $this->taxId;
    }

    public function setTaxId(?string $taxId): DebtorExternalDataEntity
    {
        $this->taxId = $taxId;

        return $this;
    }

    public function getTaxNumber(): ? string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(?string $taxNumber): DebtorExternalDataEntity
    {
        $this->taxNumber = $taxNumber;

        return $this;
    }

    public function getRegistrationNumber(): ? string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): DebtorExternalDataEntity
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getRegistrationCourt(): ? string
    {
        return $this->registrationCourt;
    }

    public function setRegistrationCourt(?string $registrationCourt): DebtorExternalDataEntity
    {
        $this->registrationCourt = $registrationCourt;

        return $this;
    }

    public function getIndustrySector(): ? string
    {
        return $this->industrySector;
    }

    public function setIndustrySector(?string $industrySector): DebtorExternalDataEntity
    {
        $this->industrySector = $industrySector;

        return $this;
    }

    public function getSubindustrySector(): ?string
    {
        return $this->subindustrySector;
    }

    public function setSubindustrySector(?string $subindustrySector): DebtorExternalDataEntity
    {
        $this->subindustrySector = $subindustrySector;

        return $this;
    }

    public function getEmployeesNumber(): ? string
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

    public function isLegalFormSoleTrader(): bool
    {
        return in_array($this->legalForm, self::LEGAL_FORMS_FOR_SOLE_TRADERS, true);
    }

    public function setLegalForm(string $legalForm): DebtorExternalDataEntity
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    public function isEstablishedCustomer(): ?bool
    {
        return $this->establishedCustomer;
    }

    public function setEstablishedCustomer(?bool $establishedCustomer): DebtorExternalDataEntity
    {
        $this->establishedCustomer = $establishedCustomer;

        return $this;
    }

    public function getMerchantExternalId(): ?string
    {
        return $this->merchantExternalId;
    }

    public function setMerchantExternalId(?string $merchantExternalId): DebtorExternalDataEntity
    {
        $this->merchantExternalId = $merchantExternalId;

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

    public function getBillingAddressId(): int
    {
        return $this->billingAddressId;
    }

    public function setBillingAddressId(int $billingAddressId): DebtorExternalDataEntity
    {
        $this->billingAddressId = $billingAddressId;

        return $this;
    }

    public function getDataHash(): string
    {
        return $this->dataHash;
    }

    public function setDataHash(string $hash): DebtorExternalDataEntity
    {
        $this->dataHash = $hash;

        return $this;
    }
}
