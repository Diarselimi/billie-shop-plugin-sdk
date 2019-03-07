<?php

namespace App\Application\UseCase\UpdateMerchantDebtorCompany;

use App\Application\UseCase\ValidatedRequestInterface;

class UpdateMerchantDebtorCompanyRequest implements ValidatedRequestInterface
{
    private $merchantDebtorExternalId;

    private $merchantId;

    private $name;

    private $addressHouse;

    private $addressStreet;

    private $addressCity;

    private $addressPostalCode;

    public function getMerchantDebtorExternalId(): string
    {
        return $this->merchantDebtorExternalId;
    }

    public function setMerchantDebtorExternalId(string $merchantDebtorExternalId): UpdateMerchantDebtorCompanyRequest
    {
        $this->merchantDebtorExternalId = $merchantDebtorExternalId;

        return $this;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): UpdateMerchantDebtorCompanyRequest
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): UpdateMerchantDebtorCompanyRequest
    {
        $this->name = $name;

        return $this;
    }

    public function getAddressHouse(): ?string
    {
        return $this->addressHouse;
    }

    public function setAddressHouse(?string $addressHouse): UpdateMerchantDebtorCompanyRequest
    {
        $this->addressHouse = $addressHouse;

        return $this;
    }

    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(?string $addressStreet): UpdateMerchantDebtorCompanyRequest
    {
        $this->addressStreet = $addressStreet;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): UpdateMerchantDebtorCompanyRequest
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressPostalCode(): ?string
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(?string $addressPostalCode): UpdateMerchantDebtorCompanyRequest
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }
}
