<?php

namespace App\DomainModel\Order\OrderContainer;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\DebtorCompany\Company;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\MostSimilarCandidateDTO;
use App\DomainModel\DebtorCompany\NullMostSimilarCandidateDTO;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorScoring\DebtorScoringResponseDTO;
use App\DomainModel\DebtorSettings\DebtorSettingsEntity;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderLineItem\OrderLineItemEntity;
use App\DomainModel\OrderRiskCheck\CheckResultCollection;
use App\DomainModel\Payment\DebtorPaymentDetailsDTO;
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

    private $invoices;

    private $merchant;

    private $merchantSettings;

    private $debtorCompany;

    private $identifiedDebtorCompany;

    private $mostSimilarCandidateDTO;

    private $dunningStatus;

    private $lineItems;

    private $paymentDetails;

    private $debtorSettings;

    private $relationLoader;

    private $riskCheckResultCollection;

    private $debtorScoringResponse;

    private ?DebtorPaymentDetailsDTO $debtorPaymentDetails = null;

    public function __construct(OrderEntity $order, OrderContainerRelationLoader $relationLoader)
    {
        $this->relationLoader = $relationLoader;
        $this->order = $order;
        $this->mostSimilarCandidateDTO = new NullMostSimilarCandidateDTO();
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

    /**
     * @return array|Invoice[]
     */
    public function getInvoices(): array
    {
        return $this->invoices
            ?? $this->invoices = $this->relationLoader->loadInvoices($this);
    }

    public function addInvoice(Invoice $invoice): OrderContainer
    {
        if (empty($this->invoices)) {
            $this->invoices = $this->relationLoader->loadInvoices($this);
        }

        $this->invoices[$invoice->getUuid()] = $invoice;

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

    public function getDebtorCompany(): Company
    {
        if (!$this->debtorCompany) {
            $this->loadDebtorCompanyAndPaymentDetails();
        }

        return $this->debtorCompany;
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

    public function setDebtorCompany(Company $debtorCompany): OrderContainer
    {
        $this->debtorCompany = $debtorCompany;

        return $this;
    }

    public function setIdentifiedDebtorCompany(DebtorCompany $identifiedDebtorCompany): OrderContainer
    {
        $this->debtorCompany = $identifiedDebtorCompany;
        $this->identifiedDebtorCompany = $identifiedDebtorCompany;

        return $this;
    }

    public function getIdentifiedDebtorCompany(): ?DebtorCompany
    {
        if (!$this->identifiedDebtorCompany) {
            $identifiedDebtor = $this->relationLoader->loadIdentifiedDebtorCompany($this);
            $this->setIdentifiedDebtorCompany($identifiedDebtor);
        }

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

    public function getDebtorPaymentDetails(): DebtorPaymentDetailsDTO
    {
        if (!$this->debtorPaymentDetails) {
            $this->loadDebtorCompanyAndPaymentDetails();
        }

        return $this->debtorPaymentDetails;
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

    public function getMostSimilarCandidateDTO(): MostSimilarCandidateDTO
    {
        return $this->mostSimilarCandidateDTO;
    }

    public function setMostSimilarCandidateDTO(MostSimilarCandidateDTO $mostSimilarCandidateDTO)
    {
        $this->mostSimilarCandidateDTO = $mostSimilarCandidateDTO;

        return $this;
    }

    private function loadDebtorCompanyAndPaymentDetails(): void
    {
        $debtorDetails = $this->relationLoader->loadDebtorDetails($this);

        $this->debtorCompany = $debtorDetails->getCompany();
        $this->debtorPaymentDetails = $debtorDetails->getPaymentDetails();
    }
}
