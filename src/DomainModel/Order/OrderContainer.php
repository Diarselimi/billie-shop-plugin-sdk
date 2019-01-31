<?php

namespace App\DomainModel\Order;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Alfred\DebtorDTO;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Person\PersonEntity;

class OrderContainer
{
    private $order;

    private $merchantDebtor;

    private $debtorPerson;

    private $debtorExternalData;

    private $debtorExternalDataAddress;

    private $debtorCompany;

    private $deliveryAddress;

    private $merchant;

    private $merchantSettings;

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public function setOrder(OrderEntity $order): OrderContainer
    {
        $this->order = $order;

        return $this;
    }

    public function getMerchantDebtor(): MerchantDebtorEntity
    {
        return $this->merchantDebtor;
    }

    public function setMerchantDebtor(MerchantDebtorEntity $merchantDebtor): OrderContainer
    {
        $this->merchantDebtor = $merchantDebtor;

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

    public function getDebtorCompany(): ? DebtorDTO
    {
        return $this->debtorCompany;
    }

    public function setDebtorCompany(DebtorDTO $debtorCompany): OrderContainer
    {
        $this->debtorCompany = $debtorCompany;

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

    public function getMerchant(): MerchantEntity
    {
        return $this->merchant;
    }

    public function setMerchant(MerchantEntity $merchant): OrderContainer
    {
        $this->merchant = $merchant;

        return $this;
    }

    public function getMerchantSettings(): MerchantSettingsEntity
    {
        return $this->merchantSettings;
    }

    public function setMerchantSettings(MerchantSettingsEntity $merchantSettings): OrderContainer
    {
        $this->merchantSettings = $merchantSettings;

        return $this;
    }
}
