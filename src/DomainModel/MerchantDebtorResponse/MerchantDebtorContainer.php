<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\DebtorLimit\DebtorCustomerLimitDTO;
use App\DomainModel\DebtorLimit\DebtorLimitDTO;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Payment\DebtorPaymentDetailsDTO;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;

class MerchantDebtorContainer
{
    private $merchantDebtor;

    private $merchant;

    private $debtorCompany;

    private $debtorLimit;

    private $debtorCustomerLimit;

    private $paymentDetails;

    private $externalId;

    private $totalCreatedOrdersAmount;

    private $totalLateOrdersAmount;

    public function __construct(
        MerchantDebtorEntity $merchantDebtor,
        MerchantEntity $merchant,
        DebtorCompany $debtorCompany,
        DebtorLimitDTO $debtorLimit,
        DebtorPaymentDetailsDTO $paymentDetails,
        float $totalCreatedOrdersAmount,
        float $totalLateOrdersAmount,
        ?string $externalId
    ) {
        $this->merchantDebtor = $merchantDebtor;
        $this->merchant = $merchant;
        $this->debtorCompany = $debtorCompany;
        $this->debtorLimit = $debtorLimit;
        $this->paymentDetails = $paymentDetails;
        $this->totalCreatedOrdersAmount = $totalCreatedOrdersAmount;
        $this->totalLateOrdersAmount = $totalLateOrdersAmount;
        $this->externalId = $externalId;
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

    public function getDebtorLimit(): DebtorLimitDTO
    {
        return $this->debtorLimit;
    }

    public function getDebtorCustomerLimit(): ? DebtorCustomerLimitDTO
    {
        return $this->debtorCustomerLimit = reset(array_filter(
            $this->debtorLimit->getDebtorCustomerLimits(),
            function (DebtorCustomerLimitDTO $debtorCustomerLimit) {
                return $debtorCustomerLimit->getCustomerCompanyUuid() === $this->merchant->getCompanyUuid();
            }
        ));
    }

    public function getPaymentDetails(): DebtorPaymentDetailsDTO
    {
        return $this->paymentDetails;
    }

    public function getExternalId(): ?string
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
