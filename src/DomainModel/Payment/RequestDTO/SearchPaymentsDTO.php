<?php

namespace App\DomainModel\Payment\RequestDTO;

class SearchPaymentsDTO
{
    private $merchantPaymentUuid;

    private $transactionUuid;

    private $paymentDebtorUuid;

    private $offset;

    private $limit;

    private $sortBy;

    private $sortDirection;

    private $keyword;

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

    public function getOffset(): string
    {
        return $this->offset;
    }

    public function setOffset(string $offset): SearchPaymentsDTO
    {
        $this->offset = $offset;

        return $this;
    }

    public function getLimit(): string
    {
        return $this->limit;
    }

    public function setLimit(string $limit): SearchPaymentsDTO
    {
        $this->limit = $limit;

        return $this;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    public function setSortBy(string $sortBy): SearchPaymentsDTO
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    public function setSortDirection(string $sortDirection): SearchPaymentsDTO
    {
        $this->sortDirection = $sortDirection;

        return $this;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(?string $keyword): SearchPaymentsDTO
    {
        $this->keyword = $keyword;

        return $this;
    }
}
