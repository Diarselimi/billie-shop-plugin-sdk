<?php

namespace App\DomainModel\DebtorCompany;

class IdentifiedDebtorCompany extends DebtorCompany
{
    public const IDENTIFIED_BY_COMPANY_ADDRESS = 'company_address';

    public const IDENTIFIED_BY_BILLING_ADDRESS = 'billing_address';

    private $identificationType;

    private $identifiedAddressUuid;

    public function getIdentificationType(): ?string
    {
        return $this->identificationType;
    }

    public function setIdentificationType(?string $identificationType): self
    {
        $this->identificationType = $identificationType;

        return $this;
    }

    public function getIdentifiedAddressUuid(): ?string
    {
        return $this->identifiedAddressUuid;
    }

    public function setIdentifiedAddressUuid(?string $identifiedAddressUuid): self
    {
        $this->identifiedAddressUuid = $identifiedAddressUuid;

        return $this;
    }
}
