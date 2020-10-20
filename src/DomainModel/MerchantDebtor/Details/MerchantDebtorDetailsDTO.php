<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantDebtor\Details;

use App\DomainModel\DebtorCompany\Company;
use App\DomainModel\Payment\DebtorPaymentDetailsDTO;

class MerchantDebtorDetailsDTO
{
    private Company $company;

    private DebtorPaymentDetailsDTO $paymentDetails;

    public function __construct(Company $company, DebtorPaymentDetailsDTO $paymentDetails)
    {
        $this->company = $company;
        $this->paymentDetails = $paymentDetails;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getPaymentDetails(): DebtorPaymentDetailsDTO
    {
        return $this->paymentDetails;
    }
}
