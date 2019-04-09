<?php

namespace App\Application\UseCase\Response;

use App\DomainModel\ArrayableInterface;

class OrderResponse implements ArrayableInterface
{
    private $externalCode;

    private $uuid;

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

    public function getExternalCode(): ? string
    {
        return $this->externalCode;
    }

    public function setExternalCode(?string $externalCode): OrderResponse
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): OrderResponse
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): OrderResponse
    {
        $this->state = $state;

        return $this;
    }

    public function getBankAccountIban(): ? string
    {
        return $this->bankAccountIban;
    }

    public function setBankAccountIban(string $bankAccountIban): OrderResponse
    {
        $this->bankAccountIban = $bankAccountIban;

        return $this;
    }

    public function getBankAccountBic(): ? string
    {
        return $this->bankAccountBic;
    }

    public function setBankAccountBic(string $bankAccountBic): OrderResponse
    {
        $this->bankAccountBic = $bankAccountBic;

        return $this;
    }

    public function getCompanyName(): ? string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): OrderResponse
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCompanyAddressHouseNumber(): ? string
    {
        return $this->companyAddressHouseNumber;
    }

    public function setCompanyAddressHouseNumber(?string $companyAddressHouseNumber): OrderResponse
    {
        $this->companyAddressHouseNumber = $companyAddressHouseNumber;

        return $this;
    }

    public function getCompanyAddressStreet(): ? string
    {
        return $this->companyAddressStreet;
    }

    public function setCompanyAddressStreet(string $companyAddressStreet): OrderResponse
    {
        $this->companyAddressStreet = $companyAddressStreet;

        return $this;
    }

    public function getCompanyAddressCity(): ? string
    {
        return $this->companyAddressCity;
    }

    public function setCompanyAddressCity(string $companyAddressCity): OrderResponse
    {
        $this->companyAddressCity = $companyAddressCity;

        return $this;
    }

    public function getCompanyAddressPostalCode(): ? string
    {
        return $this->companyAddressPostalCode;
    }

    public function setCompanyAddressPostalCode(string $companyAddressPostalCode): OrderResponse
    {
        $this->companyAddressPostalCode = $companyAddressPostalCode;

        return $this;
    }

    public function getCompanyAddressCountry(): ? string
    {
        return $this->companyAddressCountry;
    }

    public function setCompanyAddressCountry(string $companyAddressCountry): OrderResponse
    {
        $this->companyAddressCountry = $companyAddressCountry;

        return $this;
    }

    public function getDebtorExternalDataCompanyName(): string
    {
        return $this->debtorExternalDataCompanyName;
    }

    public function setDebtorExternalDataCompanyName(string $debtorExternalDataCompanyName): OrderResponse
    {
        $this->debtorExternalDataCompanyName = $debtorExternalDataCompanyName;

        return $this;
    }

    public function getDebtorExternalDataAddressCountry(): string
    {
        return $this->debtorExternalDataAddressCountry;
    }

    public function setDebtorExternalDataAddressCountry(string $debtorExternalDataAddressCountry): OrderResponse
    {
        $this->debtorExternalDataAddressCountry = $debtorExternalDataAddressCountry;

        return $this;
    }

    public function getDebtorExternalDataAddressPostalCode(): string
    {
        return $this->debtorExternalDataAddressPostalCode;
    }

    public function setDebtorExternalDataAddressPostalCode(string $debtorExternalDataAddressPostalCode): OrderResponse
    {
        $this->debtorExternalDataAddressPostalCode = $debtorExternalDataAddressPostalCode;

        return $this;
    }

    public function getDebtorExternalDataAddressStreet(): string
    {
        return $this->debtorExternalDataAddressStreet;
    }

    public function setDebtorExternalDataAddressStreet(string $debtorExternalDataAddressStreet): OrderResponse
    {
        $this->debtorExternalDataAddressStreet = $debtorExternalDataAddressStreet;

        return $this;
    }

    public function getDebtorExternalDataAddressHouse(): string
    {
        return $this->debtorExternalDataAddressHouse;
    }

    public function setDebtorExternalDataAddressHouse(string $debtorExternalDataAddressHouse): OrderResponse
    {
        $this->debtorExternalDataAddressHouse = $debtorExternalDataAddressHouse;

        return $this;
    }

    public function getDebtorExternalDataIndustrySector(): string
    {
        return $this->debtorExternalDataIndustrySector;
    }

    public function setDebtorExternalDataIndustrySector(string $debtorExternalDataIndustrySector): OrderResponse
    {
        $this->debtorExternalDataIndustrySector = $debtorExternalDataIndustrySector;

        return $this;
    }

    public function getReasons(): ?array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): OrderResponse
    {
        $this->reasons = $reasons;

        return $this;
    }

    public function getInvoiceNumber(): ? string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): OrderResponse
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getPayoutAmount(): ? float
    {
        return $this->payoutAmount;
    }

    public function setPayoutAmount(float $payoutAmount): OrderResponse
    {
        $this->payoutAmount = $payoutAmount;

        return $this;
    }

    public function getOriginalAmount(): ? float
    {
        return $this->originalAmount;
    }

    public function setOriginalAmount(float $originalAmount): OrderResponse
    {
        $this->originalAmount = $originalAmount;

        return $this;
    }

    public function getFeeAmount(): ? float
    {
        return $this->feeAmount;
    }

    public function setFeeAmount(float $feeAmount): OrderResponse
    {
        $this->feeAmount = $feeAmount;

        return $this;
    }

    public function getFeeRate(): ? float
    {
        return $this->feeRate;
    }

    public function setFeeRate(float $feeRate): OrderResponse
    {
        $this->feeRate = $feeRate;

        return $this;
    }

    public function getDueDate(): ? \DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): OrderResponse
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'external_code' => $this->getExternalCode(),
            'uuid' => $this->getUuid(),
            'state' => $this->getState(),
            'reasons' => $this->getReasons() ?: [],
            'amount' => $this->getOriginalAmount(),
            'debtor_company' => [
                'name' => $this->getCompanyName(),
                'house_number' => $this->getCompanyAddressHouseNumber(),
                'street' => $this->getCompanyAddressStreet(),
                'postal_code' => $this->getCompanyAddressPostalCode(),
                'city' => $this->getCompanyAddressCity(),
                'country' => $this->getCompanyAddressCountry(),
            ],
            'bank_account' => [
                'iban' => $this->getBankAccountIban(),
                'bic' => $this->getBankAccountBic(),
            ],
            'invoice' => [
                'number' => $this->getInvoiceNumber(),
                'payout_amount' => $this->getPayoutAmount(),
                'fee_amount' => $this->getFeeAmount(),
                'fee_rate' => $this->getFeeRate(),
                'due_date' => $this->getDueDate() ? $this->getDueDate()->format('Y-m-d') : null,
            ],
            'debtor_external_data' => [
                'name' => $this->getDebtorExternalDataCompanyName(),
                'address_country' => $this->getDebtorExternalDataAddressCountry(),
                'address_postal_code' => $this->getDebtorExternalDataAddressPostalCode(),
                'address_street' => $this->getDebtorExternalDataAddressStreet(),
                'address_house' => $this->getDebtorExternalDataAddressHouse(),
                'industry_sector' => $this->getDebtorExternalDataIndustrySector(),
            ],
        ];
    }
}
