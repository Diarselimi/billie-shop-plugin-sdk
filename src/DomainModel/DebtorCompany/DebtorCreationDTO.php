<?php

namespace App\DomainModel\DebtorCompany;

use App\DomainModel\ArrayableInterface;

class DebtorCreationDTO implements ArrayableInterface
{
    protected $name;

    protected $legalForm;

    protected $addressStreet;

    protected $addressHouse;

    protected $addressCity;

    protected $addressPostalCode;

    protected $addressCountry;

    protected $crefoId;

    protected $schufaId;

    protected $taxId;

    protected $registrationNumber;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name): DebtorCreationDTO
    {
        $this->name = $name;

        return $this;
    }

    public function getAddressHouse(): ?string
    {
        return $this->addressHouse;
    }

    public function setAddressHouse($addressHouse): DebtorCreationDTO
    {
        $this->addressHouse = $addressHouse;

        return $this;
    }

    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet($addressStreet): DebtorCreationDTO
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity($addressCity): DebtorCreationDTO
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressPostalCode(): ?string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode($addressPostalCode): DebtorCreationDTO
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry($addressCountry): DebtorCreationDTO
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber($registrationNumber): DebtorCreationDTO
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getTaxId(): ?string
    {
        return $this->taxId;
    }

    public function setTaxId($taxId): DebtorCreationDTO
    {
        $this->taxId = $taxId;

        return $this;
    }

    public function getLegalForm(): ?string
    {
        return $this->legalForm;
    }

    public function setLegalForm($legalForm): DebtorCreationDTO
    {
        $this->legalForm = $legalForm;

        return $this;
    }

    public function getCrefoId(): ?string
    {
        return $this->crefoId;
    }

    public function setCrefoId($crefoId): DebtorCreationDTO
    {
        $this->crefoId = $crefoId;

        return $this;
    }

    public function getSchufaId(): ?string
    {
        return $this->schufaId;
    }

    public function setSchufaId($schufaId): DebtorCreationDTO
    {
        $this->schufaId = $schufaId;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'legal_form' => $this->getLegalForm(),
            'address_street' => $this->getAddressStreet(),
            'address_house' => $this->getAddressHouse(),
            'address_city' => $this->getAddressCity(),
            'address_postal_code' => $this->getAddressPostalCode(),
            'address_country' => $this->getAddressCountry(),
            'crefo_id' => $this->getCrefoId(),
            'schufa_id' => $this->getSchufaId(),
            'tax_id' => $this->getTaxId(),
            'registration_number' => $this->getRegistrationNumber(),
        ];
    }
}
