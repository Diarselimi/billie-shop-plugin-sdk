<?php

namespace App\DomainModel\MerchantDebtorResponse;

use App\DomainModel\Borscht\DebtorPaymentDetailsDTO;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;

class MerchantDebtorContainer extends BaseMerchantDebtorContainer
{
    /**
     * @var string
     */
    private $merchantExternalId;

    /**
     * @var DebtorPaymentDetailsDTO
     */
    private $paymentDetails;

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
        $this->paymentDetails = $paymentDetails;
        $this->totalCreatedOrdersAmount = $totalCreatedOrdersAmount;
        $this->totalLateOrdersAmount = $totalLateOrdersAmount;

        parent::__construct($merchantDebtor, $company, $financialDetails);
    }

    public function getMerchantExternalId(): string
    {
        return $this->merchantExternalId;
    }

    public function getPaymentDetails(): DebtorPaymentDetailsDTO
    {
        return $this->paymentDetails;
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
