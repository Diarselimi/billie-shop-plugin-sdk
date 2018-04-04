<?php

namespace App\DomainModel\Company;

use App\DomainModel\AbstractEntity;

class CompanyEntity extends AbstractEntity
{
    private $merchantId;
    private $debtorId;

    public function getMerchantId()
    {
        return $this->merchantId;
    }

    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getDebtorId()
    {
        return $this->debtorId;
    }

    public function setDebtorId($debtorId)
    {
        $this->debtorId = $debtorId;

        return $this;
    }
}
