<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorLimit;

class DebtorLimitDTO
{
    private $globalFinancingLimit;

    private $globalAvailableFinancingLimit;

    private $debtorCustomerLimits;

    public function getGlobalFinancingLimit(): float
    {
        return $this->globalFinancingLimit;
    }

    public function setGlobalFinancingLimit(float $globalFinancingLimit): DebtorLimitDTO
    {
        $this->globalFinancingLimit = $globalFinancingLimit;

        return $this;
    }

    public function getGlobalAvailableFinancingLimit(): float
    {
        return $this->globalAvailableFinancingLimit;
    }

    public function setGlobalAvailableFinancingLimit(float $globalAvailableFinancingLimit): DebtorLimitDTO
    {
        $this->globalAvailableFinancingLimit = $globalAvailableFinancingLimit;

        return $this;
    }

    /**
     * @return DebtorCustomerLimitDTO[]
     */
    public function getDebtorCustomerLimits(): array
    {
        return $this->debtorCustomerLimits;
    }

    public function setDebtorCustomerLimits(array $debtorCustomerLimits): DebtorLimitDTO
    {
        $this->debtorCustomerLimits = $debtorCustomerLimits;

        return $this;
    }
}
