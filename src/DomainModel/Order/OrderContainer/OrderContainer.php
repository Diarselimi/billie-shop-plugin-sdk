<?php

namespace App\DomainModel\Order\OrderContainer;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\IdentifiedDebtorCompany;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorScoring\DebtorScoringResponseDTO;
use App\DomainModel\DebtorSettings\DebtorSettingsEntity;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderLineItem\OrderLineItemEntity;
use App\DomainModel\OrderRiskCheck\CheckResultCollection;
use App\DomainModel\Payment\OrderPaymentDetailsDTO;
use App\DomainModel\Person\PersonEntity;

class OrderContainer
{
    private $order;

    private $orderFinancialDetails;

    private $merchantDebtor;

    private $debtorPerson;

    private $debtorExternalData;

    private $debtorExternalDataAddress;

    private $deliveryAddress;

    private $billingAddress;

    private $merchant;

    private $merchantSettings;

    private $debtorCompany;

    private $identifiedDebtorCompany;

    private $dunningStatus;

    private $lineItems;

    private $paymentDetails;

    private $debtorSettings;

    private $relationLoader;

    private $riskCheckResultCollection;

    private $debtorScoringResponse;

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

    public function getBillingAddress(): AddressEntity
    {
        return $this->billingAddress
            ?? $this->billingAddress = $this->relationLoader->loadBillingAddress($this);
    }

    public function setBillingAddress(?AddressEntity $address): OrderContainer
    {
        $this->billingAddress = $address;

        return $this;
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

    public function getDebtorSettings(): ?DebtorSettingsEntity
    {
        return $this->debtorSettings ?: $this->debtorSettings = $this->relationLoader->loadDebtorSettings($this);
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

    public function setIdentifiedDebtorCompany(IdentifiedDebtorCompany $identifiedDebtorCompany): OrderContainer
    {
        $this->debtorCompany = $identifiedDebtorCompany;
        $this->identifiedDebtorCompany = $identifiedDebtorCompany;

        return $this;
    }

    public function getIdentifiedDebtorCompany(): ?IdentifiedDebtorCompany
    {
        return $this->identifiedDebtorCompany;
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
     * @return OrderLineItemEntity[]
     */
    public function getLineItems(): array
    {
        return $this->lineItems ?: $this->lineItems = $this->relationLoader->loadOrderLineItems($this);
    }

    public function setLineItems(array $lineItems): OrderContainer
    {
        $this->lineItems = $lineItems;

        return $this;
    }

    public function getPaymentDetails(): OrderPaymentDetailsDTO
    {
        return $this->paymentDetails ?: $this->paymentDetails = $this->relationLoader->loadPaymentDetails($this);
    }

    public function setPaymentDetails(OrderPaymentDetailsDTO $paymentDetails): OrderContainer
    {
        $this->paymentDetails = $paymentDetails;

        return $this;
    }

    public function getRiskCheckResultCollection(): CheckResultCollection
    {
        return $this->riskCheckResultCollection ?: $this->riskCheckResultCollection = $this->relationLoader->loadFailedRiskChecks($this);
    }

    public function setRiskCheckResultCollection(CheckResultCollection $checkResultCollection)
    {
        $this->riskCheckResultCollection = $checkResultCollection;

        return $this;
    }

    public function getDebtorScoringResponse(): ?DebtorScoringResponseDTO
    {
        return $this->debtorScoringResponse;
    }

    public function setDebtorScoringResponse(DebtorScoringResponseDTO $debtorScoringResponse): OrderContainer
    {
        $this->debtorScoringResponse = $debtorScoringResponse;

        return $this;
    }
}
