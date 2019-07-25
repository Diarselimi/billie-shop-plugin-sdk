<?php

namespace App\DomainModel\Order\OrderContainer;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorFinancialDetailsEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\Person\PersonEntity;

class OrderContainer
{
    private $order;

    private $orderFinancialDetails;

    private $merchantDebtor;

    private $merchantDebtorFinancialDetails;

    private $debtorPerson;

    private $debtorExternalData;

    private $debtorExternalDataAddress;

    private $deliveryAddress;

    private $merchant;

    private $merchantSettings;

    private $debtorCompany;

    private $dunningStatus;

    private $riskChecks;

    private $relationLoader;

    public function __construct(OrderEntity $order, OrderContainerRelationLoader $relationLoader)
    {
        $this->relationLoader = $relationLoader;
        $this->order = $order;
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public function getOrderFinancialDetails(): OrderFinancialDetailsEntity
    {
        return $this->orderFinancialDetails
            ?: $this->orderFinancialDetails = $this->relationLoader->loadOrderFinancialDetails($this)
            ;
    }

    public function getMerchantDebtor(): MerchantDebtorEntity
    {
        return $this->merchantDebtor
            ?: $this->merchantDebtor = $this->relationLoader->loadMerchantDebtor($this)
        ;
    }

    public function getMerchantDebtorFinancialDetails(): MerchantDebtorFinancialDetailsEntity
    {
        return $this->merchantDebtorFinancialDetails
            ?: $this->merchantDebtorFinancialDetails = $this->relationLoader->loadMerchantDebtorFinancialDetails($this)
        ;
    }

    public function getDebtorPerson(): PersonEntity
    {
        return $this->debtorPerson
            ?: $this->debtorPerson = $this->relationLoader->loadDebtorPerson($this)
        ;
    }

    public function getDebtorExternalData(): DebtorExternalDataEntity
    {
        return $this->debtorExternalData
            ?: $this->debtorExternalData = $this->relationLoader->loadDebtorExternalData($this)
        ;
    }

    public function getDebtorExternalDataAddress(): AddressEntity
    {
        return $this->debtorExternalDataAddress
            ?: $this->debtorExternalDataAddress = $this->relationLoader->loadDebtorExternalDataAddress($this)
        ;
    }

    public function getDeliveryAddress(): AddressEntity
    {
        return $this->deliveryAddress
            ?: $this->deliveryAddress = $this->relationLoader->loadDeliveryAddress($this)
        ;
    }

    public function getMerchant(): MerchantEntity
    {
        return $this->merchant
            ?: $this->merchant = $this->relationLoader->loadMerchant($this)
        ;
    }

    public function getMerchantSettings(): MerchantSettingsEntity
    {
        return $this->merchantSettings
            ?: $this->merchantSettings = $this->relationLoader->loadMerchantSettings($this)
        ;
    }

    public function getDebtorCompany(): DebtorCompany
    {
        return $this->debtorCompany
            ?: $this->debtorCompany = $this->relationLoader->loadDebtorCompany($this)
        ;
    }

    public function setOrderFinancialDetails(OrderFinancialDetailsEntity $orderFinancialDetails): OrderContainer
    {
        $this->orderFinancialDetails = $orderFinancialDetails;

        return $this;
    }

    public function setDebtorPerson(PersonEntity $debtorPerson): OrderContainer
    {
        $this->debtorPerson = $debtorPerson;

        return $this;
    }

    public function setDebtorExternalData(DebtorExternalDataEntity $debtorExternalData): OrderContainer
    {
        $this->debtorExternalData = $debtorExternalData;

        return $this;
    }

    public function setDebtorExternalDataAddress(AddressEntity $debtorExternalDataAddress): OrderContainer
    {
        $this->debtorExternalDataAddress = $debtorExternalDataAddress;

        return $this;
    }

    public function setDeliveryAddress(AddressEntity $deliveryAddress): OrderContainer
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function setMerchantDebtor(MerchantDebtorEntity $merchantDebtor): OrderContainer
    {
        $this->merchantDebtor = $merchantDebtor;
        $this->getOrder()->setMerchantDebtorId($merchantDebtor->getId());

        return $this;
    }

    public function setDebtorCompany(DebtorCompany $debtorCompany): OrderContainer
    {
        $this->debtorCompany = $debtorCompany;

        return $this;
    }

    public function getDunningStatus(): ?string
    {
        return $this->dunningStatus
            ?: $this->dunningStatus = $this->relationLoader->loadOrderDunningStatus($this)
            ;
    }

    public function setDunningStatus(?string $dunningStatus): OrderContainer
    {
        $this->dunningStatus = $dunningStatus;

        return $this;
    }

    /**
     * @return OrderRiskCheckEntity[]
     */
    public function getRiskChecks(): array
    {
        return $this->riskChecks ?: $this->riskChecks = $this->relationLoader->loadOrderRiskChecks($this);
    }
}
