<?php

namespace App\DomainModel\Payment\RequestDTO;

use App\Infrastructure\Graphql\AbstractSearchGraphQLDTO;

class SearchPaymentsDTO extends AbstractSearchGraphQLDTO
{
    private $merchantPaymentUuid;

    private $transactionUuid;

    private $paymentDebtorUuid;

    private $searchCompanyString;

    private $isAllocated;

    private $isOverpayment;

    public function getMerchantPaymentUuid(): string
    {
        return $this->merchantPaymentUuid;
    }

    public function setMerchantPaymentUuid(string $merchantUuid): SearchPaymentsDTO
    {
        $this->merchantPaymentUuid = $merchantUuid;

        return $this;
    }

    public function setTransactionUuid(?string $transactionUuid): SearchPaymentsDTO
    {
        $this->transactionUuid = $transactionUuid;

        return $this;
    }

    public function getTransactionUuid(): ?string
    {
        return $this->transactionUuid ? "'{$this->transactionUuid}'" : null;
    }

    public function getPaymentDebtorUuid(): ?string
    {
        return $this->paymentDebtorUuid ? "'{$this->paymentDebtorUuid}'" : null;
    }

    public function setPaymentDebtorUuid(?string $paymentDebtorUuid): SearchPaymentsDTO
    {
        $this->paymentDebtorUuid = $paymentDebtorUuid;

        return $this;
    }

    public function getSearchCompanyString(): ?string
    {
        return $this->searchCompanyString;
    }

    public function setSearchCompanyString(?string $searchCompanyString): self
    {
        $this->searchCompanyString = $searchCompanyString;

        return $this;
    }

    public function isAllocated(): ?bool
    {
        return $this->isAllocated;
    }

    public function setIsAllocated(?bool $isAllocated): self
    {
        $this->isAllocated = $isAllocated;

        return $this;
    }

    public function isOverpayment(): ?bool
    {
        return $this->isOverpayment;
    }

    public function setIsOverpayment(?bool $isOverpayment): self
    {
        $this->isOverpayment = $isOverpayment;

        return $this;
    }
}
