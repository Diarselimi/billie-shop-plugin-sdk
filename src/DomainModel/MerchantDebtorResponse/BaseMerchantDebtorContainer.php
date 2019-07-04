<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;

class BaseMerchantDebtorContainer
{
    /**
     * @var MerchantDebtorEntity
     */
    private $merchantDebtor;

    /**
     * @var DebtorCompany
     */
    private $company;

    /**
     * @var MerchantDebtorFinancialDetailsEntity
     */
    private $financialDetails;

    public function __construct(
        MerchantDebtorEntity $merchantDebtor,
        DebtorCompany $company,
        MerchantDebtorFinancialDetailsEntity $financialDetails
    ) {
        $this->merchantDebtor = $merchantDebtor;
        $this->company = $company;
        $this->financialDetails = $financialDetails;
    }

    public function getMerchantDebtor(): MerchantDebtorEntity
    {
        return $this->merchantDebtor;
    }

    public function getCompany(): DebtorCompany
    {
        return $this->company;
    }

    public function getFinancialDetails(): MerchantDebtorFinancialDetailsEntity
    {
        return $this->financialDetails;
    }
}
