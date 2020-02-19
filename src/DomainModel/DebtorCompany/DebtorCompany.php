<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\Address\AddressEntity;

class DebtorCompany
{
    private $id;

    private $uuid;

    private $name;

    private $addressHouse;

    private $addressStreet;

    private $addressPostalCode;

    private $addressCity;

    private $addressCountry;

    private $crefoId;

    private $schufaId;

    private $isBlacklisted;

    private $isStrictMatch;

    private $isTrustedSource;

    private $isSynchronized;

    private $legalForm;

    private $debtorBillingAddresses;

    private $billingAddressMatchUuid;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): DebtorCompany
    {
        $this->id = $id;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): DebtorCompany
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): DebtorCompany
    {
        $this->name = $name;

        return $this;
    }

    public function getAddressHouse(): ?string
    {
        return $this->addressHouse;
    }

    public function setAddressHouse(?string $addressHouse): DebtorCompany
    {
        $this->addressHouse = $addressHouse;

        return $this;
    }

    public function getAddressStreet(): string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(string $addressStreet): DebtorCompany
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressPostalCode(): string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(string $addressPostalCode): DebtorCompany
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCity(): string
    {
        return $this->addressCity;
    }

    public function setAddressCity(string $addressCity): DebtorCompany
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressCountry(): string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(string $addressCountry): DebtorCompany
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function getCrefoId(): ?string
    {
        return $this->crefoId;
    }

    public function setCrefoId(?string $crefoId): DebtorCompany
    {
        $this->crefoId = $crefoId;

        return $this;
    }

    public function getSchufaId(): ?string
    {
        return $this->schufaId;
    }

    public function setSchufaId(?string $schufaId): DebtorCompany
    {
        $this->schufaId = $schufaId;

        return $this;
    }

    public function isBlacklisted(): ? bool
    {
        return $this->isBlacklisted;
    }

    public function setIsBlacklisted(?bool $isBlacklisted): DebtorCompany
    {
        $this->isBlacklisted = $isBlacklisted;

        return $this;
    }

    public function isStrictMatch(): bool
    {
        return $this->isStrictMatch;
    }

    public function setIsStrictMatch(bool $isStrictMatch): DebtorCompany
    {
        $this->isStrictMatch = $isStrictMatch;

        return $this;
    }

    public function setIsTrustedSource(bool $isTrusted): DebtorCompany
    {
        $this->isTrustedSource = $isTrusted;

        return $this;
    }

    public function isTrustedSource(): bool
    {
        return $this->isTrustedSource;
    }

    public function isSynchronized(): ?bool
    {
        return $this->isSynchronized;
    }

    public function setIsSynchronized(?bool $isSynchronized): DebtorCompany
    {
        $this->isSynchronized = $isSynchronized;

        return $this;
    }

    public function getLegalForm(): ?string
    {
        return $this->legalForm;
    }

    public function setLegalForm(?string $legalForm): DebtorCompany
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    /**
     * @return AddressEntity[]
     */
    public function getDebtorBillingAddresses(): array
    {
        return $this->debtorBillingAddresses;
    }

    /**
     * @param  AddressEntity[] $debtorBillingAddresses
     * @return DebtorCompany
     */
    public function setDebtorBillingAddresses(array $debtorBillingAddresses): DebtorCompany
    {
        $this->debtorBillingAddresses = $debtorBillingAddresses;

        return $this;
    }

    public function getBillingAddressMatchUuid(): ?string
    {
        return $this->billingAddressMatchUuid;
    }

    public function setBillingAddressMatchUuid(?string $billingAddressMatchUuid): DebtorCompany
    {
        $this->billingAddressMatchUuid = $billingAddressMatchUuid;

        return $this;
    }
}
