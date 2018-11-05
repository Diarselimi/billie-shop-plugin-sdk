<?php

namespace App\Application\UseCase\GetOrder;

class GetOrderResponse
{
    private $externalCode;

    private $state;

    private $bankAccountIban;

    private $bankAccountBic;

    private $companyName;

    private $companyAddressHouseNumber;

    private $companyAddressStreet;

    private $companyAddressCity;

    private $companyAddressPostalCode;

    private $companyAddressCountry;

    private $debtorExternalDataCompanyName;

    private $debtorExternalDataAddressCountry;

    private $debtorExternalDataAddressPostalCode;

    private $debtorExternalDataAddressStreet;

    private $debtorExternalDataAddressHouse;

    private $debtorExternalDataIndustrySector;

    private $invoiceNumber;

    private $payoutAmount;

    private $originalAmount;

    private $feeAmount;

    private $feeRate;

    private $dueDate;

    private $reasons;

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function setExternalCode(string $externalCode): GetOrderResponse
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): GetOrderResponse
    {
        $this->state = $state;

        return $this;
    }

    public function getBankAccountIban(): ? string
    {
        return $this->bankAccountIban;
    }

    public function setBankAccountIban(string $bankAccountIban): GetOrderResponse
    {
        $this->bankAccountIban = $bankAccountIban;

        return $this;
    }

    public function getBankAccountBic(): ? string
    {
        return $this->bankAccountBic;
    }

    public function setBankAccountBic(string $bankAccountBic): GetOrderResponse
    {
        $this->bankAccountBic = $bankAccountBic;

        return $this;
    }

    public function getCompanyName(): ? string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): GetOrderResponse
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCompanyAddressHouseNumber(): ? string
    {
        return $this->companyAddressHouseNumber;
    }

    public function setCompanyAddressHouseNumber(?string $companyAddressHouseNumber): GetOrderResponse
    {
        $this->companyAddressHouseNumber = $companyAddressHouseNumber;

        return $this;
    }

    public function getCompanyAddressStreet(): ? string
    {
        return $this->companyAddressStreet;
    }

    public function setCompanyAddressStreet(string $companyAddressStreet): GetOrderResponse
    {
        $this->companyAddressStreet = $companyAddressStreet;

        return $this;
    }

    public function getCompanyAddressCity(): ? string
    {
        return $this->companyAddressCity;
    }

    public function setCompanyAddressCity(string $companyAddressCity): GetOrderResponse
    {
        $this->companyAddressCity = $companyAddressCity;

        return $this;
    }

    public function getCompanyAddressPostalCode(): ? string
    {
        return $this->companyAddressPostalCode;
    }

    public function setCompanyAddressPostalCode(string $companyAddressPostalCode): GetOrderResponse
    {
        $this->companyAddressPostalCode = $companyAddressPostalCode;

        return $this;
    }

    public function getCompanyAddressCountry(): ? string
    {
        return $this->companyAddressCountry;
    }

    public function setCompanyAddressCountry(string $companyAddressCountry): GetOrderResponse
    {
        $this->companyAddressCountry = $companyAddressCountry;

        return $this;
    }

    public function getDebtorExternalDataCompanyName(): string
    {
        return $this->debtorExternalDataCompanyName;
    }

    public function setDebtorExternalDataCompanyName(string $debtorExternalDataCompanyName): GetOrderResponse
    {
        $this->debtorExternalDataCompanyName = $debtorExternalDataCompanyName;

        return $this;
    }

    public function getDebtorExternalDataAddressCountry(): string
    {
        return $this->debtorExternalDataAddressCountry;
    }

    public function setDebtorExternalDataAddressCountry(string $debtorExternalDataAddressCountry): GetOrderResponse
    {
        $this->debtorExternalDataAddressCountry = $debtorExternalDataAddressCountry;

        return $this;
    }

    public function getDebtorExternalDataAddressPostalCode(): string
    {
        return $this->debtorExternalDataAddressPostalCode;
    }

    public function setDebtorExternalDataAddressPostalCode(string $debtorExternalDataAddressPostalCode): GetOrderResponse
    {
        $this->debtorExternalDataAddressPostalCode = $debtorExternalDataAddressPostalCode;

        return $this;
    }

    public function getDebtorExternalDataAddressStreet(): string
    {
        return $this->debtorExternalDataAddressStreet;
    }

    public function setDebtorExternalDataAddressStreet(string $debtorExternalDataAddressStreet): GetOrderResponse
    {
        $this->debtorExternalDataAddressStreet = $debtorExternalDataAddressStreet;

        return $this;
    }

    public function getDebtorExternalDataAddressHouse(): string
    {
        return $this->debtorExternalDataAddressHouse;
    }

    public function setDebtorExternalDataAddressHouse(string $debtorExternalDataAddressHouse): GetOrderResponse
    {
        $this->debtorExternalDataAddressHouse = $debtorExternalDataAddressHouse;

        return $this;
    }

    public function getDebtorExternalDataIndustrySector(): string
    {
        return $this->debtorExternalDataIndustrySector;
    }

    public function setDebtorExternalDataIndustrySector(string $debtorExternalDataIndustrySector): GetOrderResponse
    {
        $this->debtorExternalDataIndustrySector = $debtorExternalDataIndustrySector;

        return $this;
    }

    public function getReasons(): ?array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): GetOrderResponse
    {
        $this->reasons = $reasons;

        return $this;
    }

    public function getInvoiceNumber(): ? string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): GetOrderResponse
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getPayoutAmount(): ? float
    {
        return $this->payoutAmount;
    }

    public function setPayoutAmount(float $payoutAmount): GetOrderResponse
    {
        $this->payoutAmount = $payoutAmount;

        return $this;
    }

    public function getOriginalAmount(): ? float
    {
        return $this->originalAmount;
    }

    public function setOriginalAmount(float $originalAmount): GetOrderResponse
    {
        $this->originalAmount = $originalAmount;

        return $this;
    }

    public function getFeeAmount(): ? float
    {
        return $this->feeAmount;
    }

    public function setFeeAmount(float $feeAmount): GetOrderResponse
    {
        $this->feeAmount = $feeAmount;

        return $this;
    }

    public function getFeeRate(): ? float
    {
        return $this->feeRate;
    }

    public function setFeeRate(float $feeRate): GetOrderResponse
    {
        $this->feeRate = $feeRate;

        return $this;
    }

    public function getDueDate(): ? \DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): GetOrderResponse
    {
        $this->dueDate = $dueDate;

        return $this;
    }
}
