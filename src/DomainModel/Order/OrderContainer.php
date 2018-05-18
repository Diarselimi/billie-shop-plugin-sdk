<?php

namespace App\DomainModel\Order;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Company\CompanyEntity;
use App\DomainModel\Customer\CustomerEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\Person\PersonEntity;

class OrderContainer
{
    private $order;
    private $company;
    private $debtorPerson;
    private $debtorExternalData;
    private $debtorExternalDataAddress;
    private $deliveryAddress;
    private $customer;

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public function setOrder(OrderEntity $order): OrderContainer
    {
        $this->order = $order;

        return $this;
    }

    public function getCompany(): CompanyEntity
    {
        return $this->company;
    }

    public function setCompany(CompanyEntity $company): OrderContainer
    {
        $this->company = $company;

        return $this;
    }

    public function getDebtorPerson(): PersonEntity
    {
        return $this->debtorPerson;
    }

    public function setDebtorPerson(PersonEntity $debtorPerson): OrderContainer
    {
        $this->debtorPerson = $debtorPerson;

        return $this;
    }

    public function getDebtorExternalData(): DebtorExternalDataEntity
    {
        return $this->debtorExternalData;
    }

    public function setDebtorExternalData(DebtorExternalDataEntity $debtorExternalData): OrderContainer
    {
        $this->debtorExternalData = $debtorExternalData;

        return $this;
    }

    public function getDebtorExternalDataAddress(): AddressEntity
    {
        return $this->debtorExternalDataAddress;
    }

    public function setDebtorExternalDataAddress(AddressEntity $debtorExternalDataAddress): OrderContainer
    {
        $this->debtorExternalDataAddress = $debtorExternalDataAddress;

        return $this;
    }

    public function getDeliveryAddress(): AddressEntity
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(AddressEntity $deliveryAddress): OrderContainer
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getCustomer(): CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(CustomerEntity $customer): OrderContainer
    {
        $this->customer = $customer;

        return $this;
    }
}
