<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorLimit;

class DebtorCustomerLimitDTO
{
    private $customerCompanyUuid;

    private $financingLimit;

    private $availableFinancingLimit;

    public function getCustomerCompanyUuid(): string
    {
        return $this->customerCompanyUuid;
    }

    public function setCustomerCompanyUuid(string $customerCompanyUuid): DebtorCustomerLimitDTO
    {
        $this->customerCompanyUuid = $customerCompanyUuid;

        return $this;
    }

    public function getFinancingLimit(): float
    {
        return $this->financingLimit;
    }

    public function setFinancingLimit(float $financingLimit): DebtorCustomerLimitDTO
    {
        $this->financingLimit = $financingLimit;

        return $this;
    }

    public function getAvailableFinancingLimit(): float
    {
        return $this->availableFinancingLimit;
    }

    public function setAvailableFinancingLimit(float $availableFinancingLimit): DebtorCustomerLimitDTO
    {
        $this->availableFinancingLimit = $availableFinancingLimit;

        return $this;
    }
}
