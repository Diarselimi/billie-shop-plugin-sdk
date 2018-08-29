<?php

namespace App\DomainModel\Alfred;

class DebtorDTO
{
    private $id;
    private $name;
    private $addressHouse;
    private $addressStreet;
    private $addressPostalCode;
    private $addressCity;
    private $addressCountry;
    private $paymentId;
    private $crefoId;
    private $schufaId;
    private $isBlacklisted;
    private $isIdentifiedByPerson;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): DebtorDTO
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): DebtorDTO
    {
        $this->name = $name;

        return $this;
    }

    public function getAddressHouse(): string
    {
        return $this->addressHouse;
    }

    public function setAddressHouse(string $addressHouse): DebtorDTO
    {
        $this->addressHouse = $addressHouse;

        return $this;
    }

    public function getAddressStreet(): string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(string $addressStreet): DebtorDTO
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressPostalCode(): string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(string $addressPostalCode): DebtorDTO
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCity(): string
    {
        return $this->addressCity;
    }

    public function setAddressCity(string $addressCity): DebtorDTO
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressCountry(): string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(string $addressCountry): DebtorDTO
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    public function setPaymentId(string $paymentId): DebtorDTO
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getCrefoId(): ?string
    {
        return $this->crefoId;
    }

    public function setCrefoId(?string $crefoId): DebtorDTO
    {
        $this->crefoId = $crefoId;

        return $this;
    }

    public function getSchufaId(): ?string
    {
        return $this->schufaId;
    }

    public function setSchufaId(?string $schufaId): DebtorDTO
    {
        $this->schufaId = $schufaId;

        return $this;
    }

    public function isBlacklisted():? bool
    {
        return $this->isBlacklisted;
    }

    public function setIsBlacklisted(?bool $isBlacklisted): DebtorDTO
    {
        $this->isBlacklisted = $isBlacklisted;

        return $this;
    }

    public function isIdentifiedByPerson(): bool
    {
        return $this->isIdentifiedByPerson;
    }

    public function setIsIdentifiedByPerson(?bool $isIdentifiedByPerson = false): DebtorDTO
    {
        $this->isIdentifiedByPerson = $isIdentifiedByPerson;

        return $this;
    }
}
