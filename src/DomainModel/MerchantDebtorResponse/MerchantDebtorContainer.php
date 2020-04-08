<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
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

    private $debtorInformationChangeRequest;

    public function __construct(
        MerchantDebtorEntity $merchantDebtor,
        MerchantEntity $merchant,
        DebtorCompany $debtorCompany,
        ?DebtorLimitDTO $debtorLimit,
        DebtorPaymentDetailsDTO $paymentDetails,
        float $totalCreatedOrdersAmount,
        float $totalLateOrdersAmount,
        ?string $externalId,
        ?DebtorInformationChangeRequestEntity $debtorInformationChangeRequest
    ) {
        $this->merchantDebtor = $merchantDebtor;
        $this->merchant = $merchant;
        $this->debtorCompany = $debtorCompany;
        $this->debtorLimit = $debtorLimit;
        $this->paymentDetails = $paymentDetails;
        $this->totalCreatedOrdersAmount = $totalCreatedOrdersAmount;
        $this->totalLateOrdersAmount = $totalLateOrdersAmount;
        $this->externalId = $externalId;
        $this->debtorInformationChangeRequest = $debtorInformationChangeRequest;
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

    public function getDebtorLimit(): ? DebtorLimitDTO
    {
        return $this->debtorLimit;
    }

    public function getDebtorCustomerLimit(): ? DebtorCustomerLimitDTO
    {
        if ($this->debtorLimit === null) {
            return $this->debtorCustomerLimit = null;
        }

        $debtorCustomerLimit = array_filter(
            $this->debtorLimit->getDebtorCustomerLimits(),
            function (DebtorCustomerLimitDTO $debtorCustomerLimit) {
                return $debtorCustomerLimit->getCustomerCompanyUuid() === $this->merchant->getCompanyUuid();
            }
        );
        if (empty($debtorCustomerLimit)) {
            return $this->debtorCustomerLimit = null;
        }

        return $this->debtorCustomerLimit = reset($debtorCustomerLimit);
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

    public function getDebtorInformationChangeRequest(): ?DebtorInformationChangeRequestEntity
    {
        return $this->debtorInformationChangeRequest;
    }
}
