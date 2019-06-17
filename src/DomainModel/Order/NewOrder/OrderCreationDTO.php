<?php

namespace App\DomainModel\Order\NewOrder;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\Person\PersonEntity;

class OrderCreationDTO
{
    private $order;

    private $orderFinancialDetailsEntity;

    private $debtorPerson;

    private $debtorExternalData;

    private $debtorExternalDataAddress;

    private $deliveryAddress;

    public function __construct(
        OrderEntity $order,
        OrderFinancialDetailsEntity $orderFinancialDetailsEntity,
        PersonEntity $debtorPerson,
        DebtorExternalDataEntity $debtorExternalData,
        AddressEntity $debtorExternalDataAddress,
        AddressEntity $deliveryAddress
    ) {
        $this->order = $order;
        $this->orderFinancialDetailsEntity = $orderFinancialDetailsEntity;
        $this->debtorPerson = $debtorPerson;
        $this->debtorExternalData = $debtorExternalData;
        $this->debtorExternalDataAddress = $debtorExternalDataAddress;
        $this->deliveryAddress = $deliveryAddress;
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public function getOrderFinancialDetailsEntity(): OrderFinancialDetailsEntity
    {
        return $this->orderFinancialDetailsEntity;
    }

    public function getDebtorPerson(): PersonEntity
    {
        return $this->debtorPerson;
    }

    public function getDebtorExternalData(): DebtorExternalDataEntity
    {
        return $this->debtorExternalData;
    }

    public function getDebtorExternalDataAddress(): AddressEntity
    {
        return $this->debtorExternalDataAddress;
    }

    public function getDeliveryAddress(): AddressEntity
    {
        return $this->deliveryAddress;
    }
}
