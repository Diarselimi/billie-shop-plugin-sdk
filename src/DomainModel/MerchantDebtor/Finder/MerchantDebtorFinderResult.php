<?php

namespace App\DomainModel\MerchantDebtor\Finder;

use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;

class MerchantDebtorFinderResult
{
    private $merchantDebtor;

    private $identifiedDebtorCompany;

    public function __construct(MerchantDebtorEntity $merchantDebtor = null, IdentifiedDebtorCompany $identifiedDebtorCompany = null)
    {
        $this->merchantDebtor = $merchantDebtor;
        $this->identifiedDebtorCompany = $identifiedDebtorCompany;
    }

    public function getMerchantDebtor(): ?MerchantDebtorEntity
    {
        return $this->merchantDebtor;
    }

    public function getIdentifiedDebtorCompany(): ?IdentifiedDebtorCompany
    {
        return $this->identifiedDebtorCompany;
    }
}
