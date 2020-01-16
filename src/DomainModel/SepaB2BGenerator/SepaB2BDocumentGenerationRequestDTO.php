<?php

declare(strict_types=1);

namespace App\DomainModel\SepaB2BGenerator;

class SepaB2BDocumentGenerationRequestDTO
{
    private $bankAccountOwner;

    private $addressStreet;

    private $addressHouseNumber;

    private $addressPostcode;

    private $addressCityName;

    private $bankBic;

    private $bankIban;

    private $bankMandateReference;

    private $bankName;

    public function getBankAccountOwner(): string
    {
        return $this->bankAccountOwner;
    }

    public function setBankAccountOwner(string $bankAccountOwner): SepaB2BDocumentGenerationRequestDTO
    {
        $this->bankAccountOwner = $bankAccountOwner;

        return $this;
    }

    public function getAddressStreet(): string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(string $addressStreet): SepaB2BDocumentGenerationRequestDTO
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressHouseNumber(): ?string
    {
        return $this->addressHouseNumber;
    }

    public function setAddressHouseNumber(?string $addressHouseNumber): SepaB2BDocumentGenerationRequestDTO
    {
        $this->addressHouseNumber = $addressHouseNumber;

        return $this;
    }

    public function getAddressPostcode(): string
    {
        return $this->addressPostcode;
    }

    public function setAddressPostcode(string $addressPostcode): SepaB2BDocumentGenerationRequestDTO
    {
        $this->addressPostcode = $addressPostcode;

        return $this;
    }

    public function getAddressCityName(): string
    {
        return $this->addressCityName;
    }

    public function setAddressCityName(string $addressCityName): SepaB2BDocumentGenerationRequestDTO
    {
        $this->addressCityName = $addressCityName;

        return $this;
    }

    public function getBankBic(): string
    {
        return $this->bankBic;
    }

    public function setBankBic(string $bankBic): SepaB2BDocumentGenerationRequestDTO
    {
        $this->bankBic = $bankBic;

        return $this;
    }

    public function getBankIban(): string
    {
        return $this->bankIban;
    }

    public function setBankIban(string $bankIban): SepaB2BDocumentGenerationRequestDTO
    {
        $this->bankIban = $bankIban;

        return $this;
    }

    public function getBankMandateReference(): string
    {
        return $this->bankMandateReference;
    }

    public function setBankMandateReference(string $bankMandateReference): SepaB2BDocumentGenerationRequestDTO
    {
        $this->bankMandateReference = $bankMandateReference;

        return $this;
    }

    public function getBankName(): string
    {
        return $this->bankName;
    }

    public function setBankName($bankName): SepaB2BDocumentGenerationRequestDTO
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'mandate_reference' => $this->getBankMandateReference(),
            'bank_account_owner' => $this->getBankAccountOwner(),
            'address' => [
                'street' => $this->getAddressStreet(),
                'house_number' => $this->getAddressHouseNumber(),
                'postcode' => $this->getAddressPostcode(),
                'city_name' => $this->getAddressCityName(),
            ],
            'bic' => $this->getBankBic(),
            'iban' => $this->getBankIban(),
            'bank_name' => $this->getBankName(),
        ];
    }
}
