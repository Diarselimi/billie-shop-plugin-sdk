<?php

namespace App\DomainModel\MerchantDebtor\Finder;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;

class MerchantDebtorFinderResult
{
    private $merchantDebtor;

    private $debtorCompany;

    public function __construct(MerchantDebtorEntity $merchantDebtor = null, DebtorCompany $debtorCompany = null)
    {
        $this->merchantDebtor = $merchantDebtor;
        $this->debtorCompany = $debtorCompany;
    }

    public function getMerchantDebtor(): ?MerchantDebtorEntity
    {
        return $this->merchantDebtor;
    }

    public function getDebtorCompany(): ?DebtorCompany
    {
        return $this->debtorCompany;
    }
}
