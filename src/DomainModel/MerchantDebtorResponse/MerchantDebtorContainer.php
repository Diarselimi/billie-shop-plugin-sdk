<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Payment\DebtorPaymentDetailsDTO;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;

class MerchantDebtorContainer
{
    private $merchantDebtor;

    private $merchant;

    private $debtorCompany;

    private $financialDetails;

    private $paymentDetails;

    private $externalId;

    private $totalCreatedOrdersAmount;

    private $totalLateOrdersAmount;

    public function __construct(
        MerchantDebtorEntity $merchantDebtor,
        MerchantEntity $merchant,
        DebtorCompany $debtorCompany,
        MerchantDebtorFinancialDetailsEntity $financialDetails,
        DebtorPaymentDetailsDTO $paymentDetails,
        string $externalId,
        float $totalCreatedOrdersAmount,
        float $totalLateOrdersAmount
    ) {
        $this->merchantDebtor = $merchantDebtor;
        $this->merchant = $merchant;
        $this->debtorCompany = $debtorCompany;
        $this->financialDetails = $financialDetails;
        $this->paymentDetails = $paymentDetails;
        $this->externalId = $externalId;
        $this->totalCreatedOrdersAmount = $totalCreatedOrdersAmount;
        $this->totalLateOrdersAmount = $totalLateOrdersAmount;
    }

    public function getMerchantDebtor(): MerchantDebtorEntity
    {
        return $this->merchantDebtor;
    }

    public function getMerchant(): MerchantEntity
    {
        return $this->merchant;
    }

    public function getDebtorCompany(): DebtorCompany
    {
        return $this->debtorCompany;
    }

    public function getFinancialDetails(): MerchantDebtorFinancialDetailsEntity
    {
        return $this->financialDetails;
    }

    public function getPaymentDetails(): DebtorPaymentDetailsDTO
    {
        return $this->paymentDetails;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
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
