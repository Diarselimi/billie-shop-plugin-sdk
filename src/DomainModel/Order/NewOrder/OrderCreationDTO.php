<?php

namespace App\DomainModel\Order\NewOrder;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderLineItem\OrderLineItemEntity;
use App\DomainModel\Person\PersonEntity;

class OrderCreationDTO
{
    private $order;

    private $financialDetails;

    private $debtorPerson;

    private $debtorExternalData;

    private $debtorExternalDataAddress;

    private $deliveryAddress;

    private $billingAddress;

    private $lineItems;

    public function __construct(
        OrderEntity $order,
        OrderFinancialDetailsEntity $orderFinancialDetailsEntity,
        PersonEntity $debtorPerson,
        DebtorExternalDataEntity $debtorExternalData,
        AddressEntity $debtorExternalDataAddress,
        AddressEntity $deliveryAddress,
        AddressEntity $billingAddress,
        array $lineItems
    ) {
        $this->order = $order;
        $this->financialDetails = $orderFinancialDetailsEntity;
        $this->debtorPerson = $debtorPerson;
        $this->debtorExternalData = $debtorExternalData;
        $this->debtorExternalDataAddress = $debtorExternalDataAddress;
        $this->deliveryAddress = $deliveryAddress;
        $this->billingAddress = $billingAddress;
        $this->lineItems = $lineItems;
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public function getFinancialDetails(): OrderFinancialDetailsEntity
    {
        return $this->financialDetails;
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

    /**
     * @return OrderLineItemEntity[]
     */
    public function getLineItems(): array
    {
        return $this->lineItems;
    }

    public function getBillingAddress(): AddressEntity
    {
        return $this->billingAddress;
    }
}
