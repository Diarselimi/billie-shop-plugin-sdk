<?php

namespace App\DomainModel\Order;

use App\DomainModel\AbstractEntity;

class OrderEntity extends AbstractEntity
{
    private $amount;
    private $duration;
    private $externalCode;
    private $state;
    private $externalComment;
    private $internalComment;
    private $invoiceNumber;
    private $invoiceUrl;
    private $customersCompaniesId;
    private $deliveryAddressId;
    private $debtorPersonId;
    private $debtorExternalDataId;

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): OrderEntity
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): OrderEntity
    {
        $this->duration = $duration;

        return $this;
    }

    public function getExternalCode(): string
    {
        return $this->externalCode;
    }

    public function setExternalCode(string $externalCode): OrderEntity
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): OrderEntity
    {
        $this->state = $state;

        return $this;
    }

    public function getExternalComment():? string
    {
        return $this->externalComment;
    }

    public function setExternalComment(?string $externalComment): OrderEntity
    {
        $this->externalComment = $externalComment;

        return $this;
    }

    public function getInternalComment():? string
    {
        return $this->internalComment;
    }

    public function setInternalComment(?string $internalComment): OrderEntity
    {
        $this->internalComment = $internalComment;

        return $this;
    }

    public function getInvoiceNumber():? string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): OrderEntity
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceUrl():? string
    {
        return $this->invoiceUrl;
    }

    public function setInvoiceUrl(?string $invoiceUrl): OrderEntity
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function getCustomersCompaniesId(): int
    {
        return $this->customersCompaniesId;
    }

    public function setCustomersCompaniesId(int $customersCompaniesId): OrderEntity
    {
        $this->customersCompaniesId = $customersCompaniesId;

        return $this;
    }

    public function getDeliveryAddressId(): int
    {
        return $this->deliveryAddressId;
    }

    public function setDeliveryAddressId(int $deliveryAddressId): OrderEntity
    {
        $this->deliveryAddressId = $deliveryAddressId;

        return $this;
    }

    public function getDebtorPersonId(): int
    {
        return $this->debtorPersonId;
    }

    public function setDebtorPersonId(int $debtorPersonId): OrderEntity
    {
        $this->debtorPersonId = $debtorPersonId;

        return $this;
    }

    public function getDebtorExternalDataId(): int
    {
        return $this->debtorExternalDataId;
    }

    public function setDebtorExternalDataId(int $debtorExternalDataId): OrderEntity
    {
        $this->debtorExternalDataId = $debtorExternalDataId;

        return $this;
    }
}
