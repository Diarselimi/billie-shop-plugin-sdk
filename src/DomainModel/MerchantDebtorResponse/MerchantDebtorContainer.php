<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\Borscht\DebtorPaymentDetailsDTO;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;

class MerchantDebtorContainer
{
    /**
     * @var string
     */
    private $merchantExternalId;

    /**
     * @var MerchantDebtorEntity
     */
    private $merchantDebtor;

    /**
     * @var DebtorCompany
     */
    private $company;

    /**
     * @var DebtorPaymentDetailsDTO
     */
    private $paymentDetails;

    /**
     * @var MerchantDebtorFinancialDetailsEntity
     */
    private $financialDetails;

    /**
     * @var float
     */
    private $totalCreatedOrdersAmount;

    /**
     * @var float
     */
    private $totalLateOrdersAmount;

    public function __construct(
        string $merchantExternalId,
        MerchantDebtorEntity $merchantDebtor,
        DebtorCompany $company,
        DebtorPaymentDetailsDTO $paymentDetails,
        MerchantDebtorFinancialDetailsEntity $financialDetails,
        float $totalCreatedOrdersAmount,
        float $totalLateOrdersAmount
    ) {
        $this->merchantExternalId = $merchantExternalId;
        $this->merchantDebtor = $merchantDebtor;
        $this->company = $company;
        $this->paymentDetails = $paymentDetails;
        $this->financialDetails = $financialDetails;
        $this->totalCreatedOrdersAmount = $totalCreatedOrdersAmount;
        $this->totalLateOrdersAmount = $totalLateOrdersAmount;
    }

    public function getMerchantExternalId(): string
    {
        return $this->merchantExternalId;
    }

    public function getMerchantDebtor(): MerchantDebtorEntity
    {
        return $this->merchantDebtor;
    }

    public function getCompany(): DebtorCompany
    {
        return $this->company;
    }

    public function getPaymentDetails(): DebtorPaymentDetailsDTO
    {
        return $this->paymentDetails;
    }

    public function getFinancialDetails(): MerchantDebtorFinancialDetailsEntity
    {
        return $this->financialDetails;
    }

    public function getTotalCreatedOrdersAmount(): float
    {
        return $this->totalCreatedOrdersAmount;
    }

    public function getTotalLateOrdersAmount(): float
    {
        return $this->totalLateOrdersAmount;
    }
}
