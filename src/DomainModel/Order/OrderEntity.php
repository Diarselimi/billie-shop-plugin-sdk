<?php

namespace App\DomainModel\Order;

use App\DomainModel\AbstractEntity;

class OrderEntity extends AbstractEntity
{
    private $id;
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

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    public function getExternalCode()
    {
        return $this->externalCode;
    }

    public function setExternalCode($externalCode)
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    public function getExternalComment()
    {
        return $this->externalComment;
    }

    public function setExternalComment($externalComment)
    {
        $this->externalComment = $externalComment;

        return $this;
    }

    public function getInternalComment()
    {
        return $this->internalComment;
    }

    public function setInternalComment($internalComment)
    {
        $this->internalComment = $internalComment;

        return $this;
    }

    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceUrl()
    {
        return $this->invoiceUrl;
    }

    public function setInvoiceUrl($invoiceUrl)
    {
        $this->invoiceUrl = $invoiceUrl;

        return $this;
    }

    public function getCustomersCompaniesId()
    {
        return $this->customersCompaniesId;
    }

    public function setCustomersCompaniesId($customersCompaniesId)
    {
        $this->customersCompaniesId = $customersCompaniesId;

        return $this;
    }

    public function getDeliveryAddressId()
    {
        return $this->deliveryAddressId;
    }

    public function setDeliveryAddressId($deliveryAddressId)
    {
        $this->deliveryAddressId = $deliveryAddressId;

        return $this;
    }

    public function getDebtorPersonId()
    {
        return $this->debtorPersonId;
    }

    public function setDebtorPersonId($debtorPersonId)
    {
        $this->debtorPersonId = $debtorPersonId;

        return $this;
    }

    public function getDebtorExternalDataId()
    {
        return $this->debtorExternalDataId;
    }

    public function setDebtorExternalDataId($debtorExternalDataId)
    {
        $this->debtorExternalDataId = $debtorExternalDataId;

        return $this;
    }
}
