<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\AbstractEntity;

class MerchantDebtorEntity extends AbstractEntity
{
    private $merchantId;

    private $debtorId;

    private $paymentDebtorId;

    private $financingLimit;

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function setMerchantId(string $merchantId): MerchantDebtorEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getDebtorId(): string
    {
        return $this->debtorId;
    }

    public function setDebtorId(string $debtorId): MerchantDebtorEntity
    {
        $this->debtorId = $debtorId;

        return $this;
    }

    public function getPaymentDebtorId(): ?string
    {
        return $this->paymentDebtorId;
    }

    public function setPaymentDebtorId(?string $paymentDebtorId): MerchantDebtorEntity
    {
        $this->paymentDebtorId = $paymentDebtorId;

        return $this;
    }

    public function getFinancingLimit(): ?float
    {
        return $this->financingLimit;
    }

    public function setFinancingLimit(float $financingLimit): MerchantDebtorEntity
    {
        $this->financingLimit = $financingLimit;

        return $this;
    }

    public function increaseFinancingLimit(float $financingLimit): bool
    {
        $this->financingLimit += $financingLimit;

        return true;
    }

    public function reduceFinancingLimit(float $financingLimit): bool
    {
        $newLimit = $this->financingLimit - $financingLimit;

        //TODO: Create a Limit which can not be negative
        if ($newLimit < 0) {
            return false;
        }

        $this->financingLimit = $newLimit;

        return true;
    }
}
