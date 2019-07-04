<?php

namespace App\Application\UseCase\UpdateMerchantDebtorCompany;

use App\Application\UseCase\ValidatedRequestInterface;

class UpdateMerchantDebtorCompanyRequest implements ValidatedRequestInterface
{
    private $debtorUuid;

    private $name;

    private $addressHouse;

    private $addressStreet;

    private $addressCity;

    private $addressPostalCode;

    public function getDebtorUuid(): string
    {
        return $this->debtorUuid;
    }

    public function setDebtorUuid(string $debtorUuid): UpdateMerchantDebtorCompanyRequest
    {
        $this->debtorUuid = $debtorUuid;

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
