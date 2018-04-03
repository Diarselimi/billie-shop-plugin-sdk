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
    private $customerId;
    private $companyId;
    private $deliveryAddressId;
    private $debtorPersonId;
    private $debtorExternalDataId;
    private $paymentId;

    const STATE_NEW = 'new';

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

    public function getCustomerId():? int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): OrderEntity
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getCompanyId():? int
    {
        return $this->companyId;
    }

    public function setCompanyId(int $companyId): OrderEntity
    {
        $this->companyId = $companyId;

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

    public function getDebtorExternalDataId():? int
    {
        return $this->debtorExternalDataId;
    }

    public function setDebtorExternalDataId(int $debtorExternalDataId): OrderEntity
    {
        $this->debtorExternalDataId = $debtorExternalDataId;

        return $this;
    }

    public function getPaymentId():? string
    {
        return $this->paymentId;
    }

    public function setPaymentId(string $paymentId): OrderEntity
    {
        $this->paymentId = $paymentId;

        return $this;
    }
}
