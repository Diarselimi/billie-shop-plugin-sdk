<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorLimit;

class DebtorLimit
{
    private $globalFinancingLimit;

    private $globalAvailableFinancingLimit;

    public function getGlobalFinancingLimit(): float
    {
        return $this->globalFinancingLimit;
    }

    public function setGlobalFinancingLimit(float $globalFinancingLimit): DebtorLimit
    {
        $this->globalFinancingLimit = $globalFinancingLimit;

        return $this;
    }

    public function getGlobalAvailableFinancingLimit(): float
    {
        return $this->globalAvailableFinancingLimit;
    }

    public function setGlobalAvailableFinancingLimit(float $globalAvailableFinancingLimit): DebtorLimit
    {
        $this->globalAvailableFinancingLimit = $globalAvailableFinancingLimit;

        return $this;
    }
}
