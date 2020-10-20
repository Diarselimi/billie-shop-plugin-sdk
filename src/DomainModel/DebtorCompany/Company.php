<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\Address\AddressEntity;

class Company
{
    protected int $id;

    protected string $uuid;

    protected string $name;

    protected ?string $addressHouse;

    protected string $addressStreet;

    protected string $addressPostalCode;

    protected string $addressCity;

    protected string $addressCountry;

    protected AddressEntity $address;

    protected ?string $crefoId;

    protected ?string $schufaId;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddressHouse(): ?string
    {
        return $this->addressHouse;
    }

    public function setAddressHouse(?string $addressHouse): self
    {
        $this->addressHouse = $addressHouse;

        return $this;
    }

    public function getAddressStreet(): string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(string $addressStreet): self
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressPostalCode(): string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(string $addressPostalCode): self
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCity(): string
    {
        return $this->addressCity;
    }

    public function setAddressCity(string $addressCity): self
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressCountry(): string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(string $addressCountry): self
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function getAddress(): AddressEntity
    {
        return $this->address;
    }

    public function setAddress(AddressEntity $address): self
    {
        //TODO: support the old format; refactor later everything.
        $this->addressHouse = $address->getHouseNumber();
        $this->addressCity = $address->getCity();
        $this->addressPostalCode = $address->getPostalCode();
        $this->addressStreet = $address->getStreet();
        $this->addressCountry = $address->getCountry();

        $this->address = $address;

        return $this;
    }

    public function getCrefoId(): ?string
    {
        return $this->crefoId;
    }

    public function setCrefoId(?string $crefoId): self
    {
        $this->crefoId = $crefoId;

        return $this;
    }

    public function getSchufaId(): ?string
    {
        return $this->schufaId;
    }

    public function setSchufaId(?string $schufaId): self
    {
        $this->schufaId = $schufaId;

        return $this;
    }
}
