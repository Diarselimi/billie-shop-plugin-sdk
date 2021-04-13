<?php

namespace App\DomainModel\OrderResponse;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;
use Ozean12\Money\TaxedMoney\TaxedMoney;

/**
 * @OA\Schema(schema="OrderResponseV2", title="Order Entity", type="object", properties={
 *      @OA\Property(property="external_code", type="string", nullable=true, example="C-10123456789-0001"),
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="state", ref="#/components/schemas/OrderStateV2", example="created"),
 *      @OA\Property(property="decline_reason", ref="#/components/schemas/OrderDeclineReason", nullable=true),
 *      @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *      @OA\Property(property="unshipped_amount", ref="#/components/schemas/AmountDTO"),
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration", example=30),
 *      @OA\Property(property="debtor_company_name", type="string", example="Company name"),
 *      @OA\Property(property="debtor_company_address", ref="#/components/schemas/Address"),
 *      @OA\Property(property="delivery_address", ref="#/components/schemas/Address"),
 *      @OA\Property(property="billing_address", ref="#/components/schemas/Address"),
 *
 *      @OA\Property(property="bank_account", type="object", properties={
 *          @OA\Property(property="iban", ref="#/components/schemas/TinyText", nullable=true, description="Virtual IBAN provided by Billie"),
 *          @OA\Property(property="bic", ref="#/components/schemas/TinyText", nullable=true),
 *      }),
 *
 *      @OA\Property(property="debtor_external_data", description="Data provided in the order creation", type="object", properties={
 *          @OA\Property(property="merchant_customer_id", ref="#/components/schemas/TinyText", example="C-10123456789"),
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Billie G.m.b.H."),
 *          @OA\Property(property="industry_sector", ref="#/components/schemas/TinyText", nullable=true),
 *      }),
 *      @OA\Property(property="debtor_external_address", ref="#/components/schemas/Address"),
 *
 *      @OA\Property(property="created_at", ref="#/components/schemas/DateTime"),
 *      @OA\Property(
 *          property="invoices",
 *          type="array",
 *          nullable=true,
 *          @OA\Items(ref="#/components/schemas/OrderInvoiceResponse")
 *      )
 * })
 *
 */
class OrderResponseV2 implements ArrayableInterface
{
    private $externalCode;

    private $uuid;

    private $state;

    private $bankAccountIban;

    private $bankAccountBic;

    private $companyName;

    private $companyAddressHouseNumber;

    private $companyAddressStreet;

    private $companyAddressCity;

    private $companyAddressPostalCode;

    private $companyAddressCountry;

    private $debtorExternalDataCompanyName;

    private $debtorExternalDataAddressCountry;

    private $debtorExternalDataAddressCity;

    private $debtorExternalDataAddressPostalCode;

    private $debtorExternalDataAddressStreet;

    private $debtorExternalDataAddressHouse;

    private $debtorExternalDataIndustrySector;

    private $declineReason;

    private $amount;

    private $createdAt;

    private $debtorExternalDataCustomerId;

    private $shippedAt;

    private $deliveryAddressStreet;

    private $deliveryAddressHouseNumber;

    private $deliveryAddressPostalCode;

    private $deliveryAddressCity;

    private $deliveryAddressCountry;

    private $billingAddressStreet;

    private $billingAddressHouseNumber;

    private $billingAddressPostalCode;

    private $billingAddressCity;

    private $billingAddressCountry;

    private $duration;

    private $dunningStatus;

    private $debtorUuid;

    private $invoiceNumber;

    private $payoutAmount;

    private $outstandingAmount;

    private $feeAmount;

    private $feeRate;

    private $dueDate;

    private $pendingMerchantPaymentAmount;

    private $pendingCancellationAmount;

    private string $workflowName;

    private TaxedMoney $unshippedAmount;

    private array

        $invoices = [];

    /**
     * @deprecated use declineReason
     */
    private $reasons = [];

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    public function setExternalCode(?string $externalCode): self
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getBankAccountIban(): ?string
    {
        return $this->bankAccountIban;
    }

    public function setBankAccountIban(string $bankAccountIban): self
    {
        $this->bankAccountIban = $bankAccountIban;

        return $this;
    }

    public function getBankAccountBic(): ?string
    {
        return $this->bankAccountBic;
    }

    public function setBankAccountBic(string $bankAccountBic): self
    {
        $this->bankAccountBic = $bankAccountBic;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCompanyAddressHouseNumber(): ?string
    {
        return $this->companyAddressHouseNumber;
    }

    public function setCompanyAddressHouseNumber(?string $companyAddressHouseNumber): self
    {
        $this->companyAddressHouseNumber = $companyAddressHouseNumber;

        return $this;
    }

    public function getCompanyAddressStreet(): ?string
    {
        return $this->companyAddressStreet;
    }

    public function setCompanyAddressStreet(string $companyAddressStreet): self
    {
        $this->companyAddressStreet = $companyAddressStreet;

        return $this;
    }

    public function getCompanyAddressCity(): ?string
    {
        return $this->companyAddressCity;
    }

    public function setCompanyAddressCity(string $companyAddressCity): self
    {
        $this->companyAddressCity = $companyAddressCity;

        return $this;
    }

    public function getCompanyAddressPostalCode(): ?string
    {
        return $this->companyAddressPostalCode;
    }

    public function setCompanyAddressPostalCode(string $companyAddressPostalCode): self
    {
        $this->companyAddressPostalCode = $companyAddressPostalCode;

        return $this;
    }

    public function getCompanyAddressCountry(): ?string
    {
        return $this->companyAddressCountry;
    }

    public function setCompanyAddressCountry(string $companyAddressCountry): self
    {
        $this->companyAddressCountry = $companyAddressCountry;

        return $this;
    }

    public function getDebtorExternalDataCompanyName(): string
    {
        return $this->debtorExternalDataCompanyName;
    }

    public function setDebtorExternalDataCompanyName(string $debtorExternalDataCompanyName): self
    {
        $this->debtorExternalDataCompanyName = $debtorExternalDataCompanyName;

        return $this;
    }

    public function getDebtorExternalDataAddressCountry(): string
    {
        return $this->debtorExternalDataAddressCountry;
    }

    public function setDebtorExternalDataAddressCountry(string $debtorExternalDataAddressCountry): self
    {
        $this->debtorExternalDataAddressCountry = $debtorExternalDataAddressCountry;

        return $this;
    }

    public function getDebtorExternalDataAddressCity(): string
    {
        return $this->debtorExternalDataAddressCity;
    }

    public function setDebtorExternalDataAddressCity(string $debtorExternalDataAddressCity): self
    {
        $this->debtorExternalDataAddressCity = $debtorExternalDataAddressCity;

        return $this;
    }

    public function getDebtorExternalDataAddressPostalCode(): string
    {
        return $this->debtorExternalDataAddressPostalCode;
    }

    public function setDebtorExternalDataAddressPostalCode(string $debtorExternalDataAddressPostalCode): self
    {
        $this->debtorExternalDataAddressPostalCode = $debtorExternalDataAddressPostalCode;

        return $this;
    }

    public function getDebtorExternalDataAddressStreet(): string
    {
        return $this->debtorExternalDataAddressStreet;
    }

    public function setDebtorExternalDataAddressStreet(string $debtorExternalDataAddressStreet): self
    {
        $this->debtorExternalDataAddressStreet = $debtorExternalDataAddressStreet;

        return $this;
    }

    public function getDebtorExternalDataAddressHouse(): ?string
    {
        return $this->debtorExternalDataAddressHouse;
    }

    public function setDebtorExternalDataAddressHouse(?string $debtorExternalDataAddressHouse): self
    {
        $this->debtorExternalDataAddressHouse = $debtorExternalDataAddressHouse;

        return $this;
    }

    public function getDebtorExternalDataIndustrySector(): ?string
    {
        return $this->debtorExternalDataIndustrySector;
    }

    public function setDebtorExternalDataIndustrySector(?string $debtorExternalDataIndustrySector): self
    {
        $this->debtorExternalDataIndustrySector = $debtorExternalDataIndustrySector;

        return $this;
    }

    public function getDeclineReason(): ?string
    {
        return $this->declineReason;
    }

    public function setDeclineReason(string $declineReason): self
    {
        $this->declineReason = $declineReason;

        return $this;
    }

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(TaxedMoney $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getUnshippedAmount(): TaxedMoney
    {
        return $this->unshippedAmount;
    }

    public function setUnshippedAmount(TaxedMoney $unshippedAmount): self
    {
        $this->unshippedAmount = $unshippedAmount;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDebtorExternalDataCustomerId(): ?string
    {
        return $this->debtorExternalDataCustomerId;
    }

    public function setDebtorExternalDataCustomerId(?string $debtorExternalDataCustomerId): self
    {
        $this->debtorExternalDataCustomerId = $debtorExternalDataCustomerId;

        return $this;
    }

    public function getShippedAt(): ?\DateTime
    {
        return $this->shippedAt;
    }

    public function setShippedAt(?\DateTime $shippedAt): self
    {
        $this->shippedAt = $shippedAt;

        return $this;
    }

    public function getDeliveryAddressStreet(): ?string
    {
        return $this->deliveryAddressStreet;
    }

    public function setDeliveryAddressStreet(?string $street): self
    {
        $this->deliveryAddressStreet = $street;

        return $this;
    }

    public function getDeliveryAddressHouseNumber(): ?string
    {
        return $this->deliveryAddressHouseNumber;
    }

    public function setDeliveryAddressHouseNumber(?string $deliveryAddressHouseNumber): self
    {
        $this->deliveryAddressHouseNumber = $deliveryAddressHouseNumber;

        return $this;
    }

    public function getDeliveryAddressPostalCode(): ?string
    {
        return $this->deliveryAddressPostalCode;
    }

    public function setDeliveryAddressPostalCode(?string $deliveryAddressPostalCode): self
    {
        $this->deliveryAddressPostalCode = $deliveryAddressPostalCode;

        return $this;
    }

    public function getDeliveryAddressCity(): ?string
    {
        return $this->deliveryAddressCity;
    }

    public function setDeliveryAddressCity(?string $deliveryAddressCity): self
    {
        $this->deliveryAddressCity = $deliveryAddressCity;

        return $this;
    }

    public function getDeliveryAddressCountry(): ?string
    {
        return $this->deliveryAddressCountry;
    }

    public function setDeliveryAddressCountry(?string $deliveryAddressCountry): self
    {
        $this->deliveryAddressCountry = $deliveryAddressCountry;

        return $this;
    }

    public function getBillingAddressStreet(): ?string
    {
        return $this->billingAddressStreet;
    }

    public function setBillingAddressStreet(?string $deliveryBillingStreet): self
    {
        $this->billingAddressStreet = $deliveryBillingStreet;

        return $this;
    }

    public function getBillingAddressHouseNumber(): ?string
    {
        return $this->billingAddressHouseNumber;
    }

    public function setBillingAddressHouseNumber(?string $deliveryBillingHouseNumber): self
    {
        $this->billingAddressHouseNumber = $deliveryBillingHouseNumber;

        return $this;
    }

    public function getBillingAddressPostalCode(): ?string
    {
        return $this->billingAddressPostalCode;
    }

    public function setBillingAddressPostalCode(?string $deliveryBillingPostalCode): self
    {
        $this->billingAddressPostalCode = $deliveryBillingPostalCode;

        return $this;
    }

    public function getBillingAddressCity(): ?string
    {
        return $this->billingAddressCity;
    }

    public function setBillingAddressCity(?string $deliveryBillingCity): self
    {
        $this->billingAddressCity = $deliveryBillingCity;

        return $this;
    }

    public function getBillingAddressCountry(): ?string
    {
        return $this->billingAddressCountry;
    }

    public function setBillingAddressCountry(?string $deliveryBillingCountry): self
    {
        $this->billingAddressCountry = $deliveryBillingCountry;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDunningStatus(): ?string
    {
        return $this->dunningStatus;
    }

    public function setDunningStatus(?string $dunningStatus): self
    {
        $this->dunningStatus = $dunningStatus;

        return $this;
    }

    public function getDebtorUuid(): ?string
    {
        return $this->debtorUuid;
    }

    public function setDebtorUuid(?string $debtorUuid): self
    {
        $this->debtorUuid = $debtorUuid;

        return $this;
    }

    /**
     * @deprecated
     */
    public function getReasons(): ?array
    {
        return array_filter($this->reasons);
    }

    /**
     * @deprecated
     */
    public function setReasons(array $reasons): OrderResponseV2
    {
        $this->reasons = $reasons;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): OrderResponseV2
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getPayoutAmount(): ?float
    {
        return $this->payoutAmount;
    }

    public function setPayoutAmount(float $payoutAmount): OrderResponseV2
    {
        $this->payoutAmount = $payoutAmount;

        return $this;
    }

    public function getOutstandingAmount(): ?float
    {
        return $this->outstandingAmount;
    }

    public function setOutstandingAmount(float $outstandingAmount): OrderResponseV2
    {
        $this->outstandingAmount = $outstandingAmount;

        return $this;
    }

    public function getFeeAmount(): ?float
    {
        return $this->feeAmount;
    }

    public function setFeeAmount(float $feeAmount): OrderResponseV2
    {
        $this->feeAmount = $feeAmount;

        return $this;
    }

    public function getFeeRate(): ?float
    {
        return $this->feeRate;
    }

    public function setFeeRate(float $feeRate): OrderResponseV2
    {
        $this->feeRate = $feeRate;

        return $this;
    }

    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): OrderResponseV2
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getPendingMerchantPaymentAmount(): ?float
    {
        return $this->pendingMerchantPaymentAmount;
    }

    public function setPendingMerchantPaymentAmount(?float $pendingMerchantPaymentAmount): OrderResponseV2
    {
        $this->pendingMerchantPaymentAmount = $pendingMerchantPaymentAmount;

        return $this;
    }

    public function getPendingCancellationAmount(): ?float
    {
        return $this->pendingCancellationAmount;
    }

    public function setPendingCancellationAmount(?float $pendingCancellationAmount): OrderResponseV2
    {
        $this->pendingCancellationAmount = $pendingCancellationAmount;

        return $this;
    }

    public function getWorkflowName(): string
    {
        return $this->workflowName;
    }

    public function setWorkflowName(string $workflowName): self
    {
        $this->workflowName = $workflowName;

        return $this;
    }

    public function getInvoices(): array
    {
        return $this->invoices;
    }

    public function setInvoices(array $invoices): self
    {
        $this->invoices = $invoices;

        return $this;
    }

    public function addInvoice(OrderInvoiceResponse $invoices): self
    {
        $this->invoices[] = $invoices;

        return $this;
    }

    public function toArray(): array
    {
        return [];
    }
}
