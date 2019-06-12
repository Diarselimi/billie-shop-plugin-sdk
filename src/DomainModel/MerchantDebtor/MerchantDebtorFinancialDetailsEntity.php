<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsException;
use Billie\PdoBundle\DomainModel\AbstractEntity;

class MerchantDebtorFinancialDetailsEntity extends AbstractEntity
{
    private $merchantDebtorId;

    private $financingLimit;

    private $financingPower;

    private $createdAt;

    public function getMerchantDebtorId(): int
    {
        return $this->merchantDebtorId;
    }

    public function setMerchantDebtorId(int $merchantDebtorId): MerchantDebtorFinancialDetailsEntity
    {
        $this->merchantDebtorId = $merchantDebtorId;

        return $this;
    }

    public function getFinancingLimit(): float
    {
        return $this->financingLimit;
    }

    public function setFinancingLimit(float $financingLimit): MerchantDebtorFinancialDetailsEntity
    {
        $this->financingLimit = $financingLimit;

        return $this;
    }

    public function getFinancingPower(): float
    {
        return $this->financingPower;
    }

    public function setFinancingPower(float $financingPower): MerchantDebtorFinancialDetailsEntity
    {
        $this->financingPower = $financingPower;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): MerchantDebtorFinancialDetailsEntity
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function reduceFinancingPower(float $difference): void
    {
        $newPower = $this->financingPower - $difference;
        if ($newPower < 0) {
            throw new MerchantDebtorLimitsException('Trying to set negative financing power');
        }

        $this->financingPower = $newPower;
    }

    public function increaseFinancingPower(float $difference): void
    {
        $newPower = $this->financingPower + $difference;
        if ($newPower > $this->financingLimit) {
            throw new MerchantDebtorLimitsException('Trying to set excessive financing power');
        }

        $this->financingPower = $newPower;
    }
}
