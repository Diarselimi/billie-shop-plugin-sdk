<?php

namespace App\DomainModel\MerchantDebtor;

class RegisterDebtorDTO
{
    private $merchantPaymentUuid;

    private $companyUuid;

    public function __construct(string $merchantPaymentUuid, string $companyUuid)
    {
        $this->merchantPaymentUuid = $merchantPaymentUuid;
        $this->companyUuid = $companyUuid;
    }

    public function getMerchantPaymentUuid(): string
    {
        return $this->merchantPaymentUuid;
    }

    public function getCompanyUuid(): string
    {
        return $this->companyUuid;
    }
}
