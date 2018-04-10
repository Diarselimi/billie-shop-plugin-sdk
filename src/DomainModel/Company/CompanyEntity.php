<?php

namespace App\DomainModel\Company;

use App\DomainModel\AbstractEntity;

class CompanyEntity extends AbstractEntity
{
    private $merchantId;
    private $debtorId;

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function setMerchantId(string $merchantId): CompanyEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getDebtorId(): string
    {
        return $this->debtorId;
    }

    public function setDebtorId(string $debtorId): CompanyEntity
    {
        $this->debtorId = $debtorId;

        return $this;
    }
}
