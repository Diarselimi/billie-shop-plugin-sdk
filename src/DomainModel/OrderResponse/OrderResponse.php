<?php

namespace App\DomainModel\OrderResponse;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="OrderResponse", title="Order Entity", type="object", properties={
 *      @OA\Property(property="order_id", type="string", nullable=true, example="C-10123456789-0001"),
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="state", ref="#/components/schemas/OrderState", example="created"),
 *      @OA\Property(property="reasons", ref="#/components/schemas/OrderDeclineReason", nullable=true),
 *      @OA\Property(property="amount", type="number", format="float", nullable=false, example=123.45),
 *      @OA\Property(property="amount_tax", type="number", format="float", nullable=false, example=123.45),
 *      @OA\Property(property="amount_net", type="number", format="float", nullable=false, example=123.45),
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration", example=30),
 *      @OA\Property(property="dunning_status", ref="#/components/schemas/OrderDunningStatus"),
 *
 *      @OA\Property(property="debtor_company", type="object", description="Identified company", properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", nullable=true, example="Billie GmbH"),
 *          @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText", nullable=true, example="4"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", nullable=true, example="Charlottenstr."),
 *          @OA\Property(property="address_postal_code", type="string", nullable=true, maxLength=5, example="10969"),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", nullable=true, example="Berlin"),
 *          @OA\Property(property="address_country", type="string", nullable=true, maxLength=2),
 *      }),
 *
 *      @OA\Property(property="bank_account", type="object", properties={
 *          @OA\Property(property="iban", ref="#/components/schemas/TinyText", nullable=true, description="Virtual IBAN provided by Billie"),
 *          @OA\Property(property="bic", ref="#/components/schemas/TinyText", nullable=true),
 *      }),
 *
 *      @OA\Property(property="invoice", type="object", properties={
 *          @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="payout_amount", type="number", format="float", nullable=true),
 *          @OA\Property(property="outstanding_amount", type="number", format="float", nullable=true),
 *          @OA\Property(property="fee_amount", type="number", format="float", nullable=true),
 *          @OA\Property(property="fee_rate", type="number", format="float", nullable=true),
 *          @OA\Property(property="due_date", type="string", format="date", nullable=true, example="2019-03-20"),
 *      }),
 *
 *      @OA\Property(property="debtor_external_data", description="Data provided in the order creation", type="object", properties={
 *          @OA\Property(property="merchant_customer_id", ref="#/components/schemas/TinyText", example="C-10123456789"),
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Billie G.m.b.H."),
 *          @OA\Property(property="address_country", type="string", maxLength=2, example="DE"),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", example="Berlin"),
 *          @OA\Property(property="address_postal_code", type="string", maxLength=5, example="10969"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", example="Charlotten Straße"),
 *          @OA\Property(property="address_house", ref="#/components/schemas/TinyText", example="4"),
 *          @OA\Property(property="industry_sector", ref="#/components/schemas/TinyText", nullable=true),
 *      }),
 *
 *      @OA\Property(property="delivery_address", type="object", ref="#/components/schemas/CreateOrderAddressRequest"),
 *      @OA\Property(property="billing_address", type="object", ref="#/components/schemas/CreateOrderAddressRequest"),
 *      @OA\Property(property="created_at", ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="shipped_at", ref="#/components/schemas/DateTime"),
 * })
 */
class OrderResponse implements ArrayableInterface
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

    private $invoiceNumber;

    private $payoutAmount;

    private $outstandingAmount;

    private $amountGross;

    private $amountTax;

    private $amountNet;

    private $feeAmount;

    private $feeRate;

    private $dueDate;

    private $reasons;

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

    public function getExternalCode(): ? string
    {
        return $this->externalCode;
    }

    public function setExternalCode(?string $externalCode): OrderResponse
    {
        $this->externalCode = $externalCode;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): OrderResponse
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): OrderResponse
    {
        $this->state = $state;

        return $this;
    }

    public function getBankAccountIban(): ? string
    {
        return $this->bankAccountIban;
    }

    public function setBankAccountIban(string $bankAccountIban): OrderResponse
    {
        $this->bankAccountIban = $bankAccountIban;

        return $this;
    }

    public function getBankAccountBic(): ? string
    {
        return $this->bankAccountBic;
    }

    public function setBankAccountBic(string $bankAccountBic): OrderResponse
    {
        $this->bankAccountBic = $bankAccountBic;

        return $this;
    }

    public function getCompanyName(): ? string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): OrderResponse
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCompanyAddressHouseNumber(): ? string
    {
        return $this->companyAddressHouseNumber;
    }

    public function setCompanyAddressHouseNumber(?string $companyAddressHouseNumber): OrderResponse
    {
        $this->companyAddressHouseNumber = $companyAddressHouseNumber;

        return $this;
    }

    public function getCompanyAddressStreet(): ? string
    {
        return $this->companyAddressStreet;
    }

    public function setCompanyAddressStreet(string $companyAddressStreet): OrderResponse
    {
        $this->companyAddressStreet = $companyAddressStreet;

        return $this;
    }

    public function getCompanyAddressCity(): ? string
    {
        return $this->companyAddressCity;
    }

    public function setCompanyAddressCity(string $companyAddressCity): OrderResponse
    {
        $this->companyAddressCity = $companyAddressCity;

        return $this;
    }

    public function getCompanyAddressPostalCode(): ? string
    {
        return $this->companyAddressPostalCode;
    }

    public function setCompanyAddressPostalCode(string $companyAddressPostalCode): OrderResponse
    {
        $this->companyAddressPostalCode = $companyAddressPostalCode;

        return $this;
    }

    public function getCompanyAddressCountry(): ? string
    {
        return $this->companyAddressCountry;
    }

    public function setCompanyAddressCountry(string $companyAddressCountry): OrderResponse
    {
        $this->companyAddressCountry = $companyAddressCountry;

        return $this;
    }

    public function getDebtorExternalDataCompanyName(): string
    {
        return $this->debtorExternalDataCompanyName;
    }

    public function setDebtorExternalDataCompanyName(string $debtorExternalDataCompanyName): OrderResponse
    {
        $this->debtorExternalDataCompanyName = $debtorExternalDataCompanyName;

        return $this;
    }

    public function getDebtorExternalDataAddressCountry(): string
    {
        return $this->debtorExternalDataAddressCountry;
    }

    public function setDebtorExternalDataAddressCountry(string $debtorExternalDataAddressCountry): OrderResponse
    {
        $this->debtorExternalDataAddressCountry = $debtorExternalDataAddressCountry;

        return $this;
    }

    public function getDebtorExternalDataAddressCity(): string
    {
        return $this->debtorExternalDataAddressCity;
    }

    public function setDebtorExternalDataAddressCity(string $debtorExternalDataAddressCity): OrderResponse
    {
        $this->debtorExternalDataAddressCity = $debtorExternalDataAddressCity;

        return $this;
    }

    public function getDebtorExternalDataAddressPostalCode(): string
    {
        return $this->debtorExternalDataAddressPostalCode;
    }

    public function setDebtorExternalDataAddressPostalCode(string $debtorExternalDataAddressPostalCode): OrderResponse
    {
        $this->debtorExternalDataAddressPostalCode = $debtorExternalDataAddressPostalCode;

        return $this;
    }

    public function getDebtorExternalDataAddressStreet(): string
    {
        return $this->debtorExternalDataAddressStreet;
    }

    public function setDebtorExternalDataAddressStreet(string $debtorExternalDataAddressStreet): OrderResponse
    {
        $this->debtorExternalDataAddressStreet = $debtorExternalDataAddressStreet;

        return $this;
    }

    public function getDebtorExternalDataAddressHouse(): ? string
    {
        return $this->debtorExternalDataAddressHouse;
    }

    public function setDebtorExternalDataAddressHouse(?string $debtorExternalDataAddressHouse): OrderResponse
    {
        $this->debtorExternalDataAddressHouse = $debtorExternalDataAddressHouse;

        return $this;
    }

    public function getDebtorExternalDataIndustrySector(): ? string
    {
        return $this->debtorExternalDataIndustrySector;
    }

    public function setDebtorExternalDataIndustrySector(?string $debtorExternalDataIndustrySector): OrderResponse
    {
        $this->debtorExternalDataIndustrySector = $debtorExternalDataIndustrySector;

        return $this;
    }

    public function getReasons(): ?array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): OrderResponse
    {
        $this->reasons = $reasons;

        return $this;
    }

    public function getInvoiceNumber(): ? string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): OrderResponse
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getPayoutAmount(): ? float
    {
        return $this->payoutAmount;
    }

    public function setPayoutAmount(float $payoutAmount): OrderResponse
    {
        $this->payoutAmount = $payoutAmount;

        return $this;
    }

    public function getOutstandingAmount(): ? float
    {
        return $this->outstandingAmount;
    }

    public function setOutstandingAmount(float $outstandingAmount): OrderResponse
    {
        $this->outstandingAmount = $outstandingAmount;

        return $this;
    }

    public function getAmountGross(): float
    {
        return $this->amountGross;
    }

    public function setAmountGross(float $amountGross): OrderResponse
    {
        $this->amountGross = $amountGross;

        return $this;
    }

    public function getAmountTax(): float
    {
        return $this->amountTax;
    }

    public function setAmountTax(float $amountTax): OrderResponse
    {
        $this->amountTax = $amountTax;

        return $this;
    }

    public function getAmountNet(): float
    {
        return $this->amountNet;
    }

    public function setAmountNet(float $amountNet): OrderResponse
    {
        $this->amountNet = $amountNet;

        return $this;
    }

    public function getFeeAmount(): ? float
    {
        return $this->feeAmount;
    }

    public function setFeeAmount(float $feeAmount): OrderResponse
    {
        $this->feeAmount = $feeAmount;

        return $this;
    }

    public function getFeeRate(): ? float
    {
        return $this->feeRate;
    }

    public function setFeeRate(float $feeRate): OrderResponse
    {
        $this->feeRate = $feeRate;

        return $this;
    }

    public function getDueDate(): ? \DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): OrderResponse
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): OrderResponse
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDebtorExternalDataCustomerId(): ?string
    {
        return $this->debtorExternalDataCustomerId;
    }

    public function setDebtorExternalDataCustomerId(?string $debtorExternalDataCustomerId): OrderResponse
    {
        $this->debtorExternalDataCustomerId = $debtorExternalDataCustomerId;

        return $this;
    }

    public function getShippedAt(): ?\DateTime
    {
        return $this->shippedAt;
    }

    public function setShippedAt(?\DateTime $shippedAt): OrderResponse
    {
        $this->shippedAt = $shippedAt;

        return $this;
    }

    public function getDeliveryAddressStreet(): ?string
    {
        return $this->deliveryAddressStreet;
    }

    public function setDeliveryAddressStreet(?string $street): OrderResponse
    {
        $this->deliveryAddressStreet = $street;

        return $this;
    }

    public function getDeliveryAddressHouseNumber(): ?string
    {
        return $this->deliveryAddressHouseNumber;
    }

    public function setDeliveryAddressHouseNumber(?string $deliveryAddressHouseNumber): OrderResponse
    {
        $this->deliveryAddressHouseNumber = $deliveryAddressHouseNumber;

        return $this;
    }

    public function getDeliveryAddressPostalCode(): ?string
    {
        return $this->deliveryAddressPostalCode;
    }

    public function setDeliveryAddressPostalCode(?string $deliveryAddressPostalCode): OrderResponse
    {
        $this->deliveryAddressPostalCode = $deliveryAddressPostalCode;

        return $this;
    }

    public function getDeliveryAddressCity(): ?string
    {
        return $this->deliveryAddressCity;
    }

    public function setDeliveryAddressCity(?string $deliveryAddressCity): OrderResponse
    {
        $this->deliveryAddressCity = $deliveryAddressCity;

        return $this;
    }

    public function getDeliveryAddressCountry(): ?string
    {
        return $this->deliveryAddressCountry;
    }

    public function setDeliveryAddressCountry(?string $deliveryAddressCountry): OrderResponse
    {
        $this->deliveryAddressCountry = $deliveryAddressCountry;

        return $this;
    }

    public function getBillingAddressStreet(): ?string
    {
        return $this->billingAddressStreet;
    }

    public function setBillingAddressStreet(?string $deliveryBillingStreet): OrderResponse
    {
        $this->billingAddressStreet = $deliveryBillingStreet;

        return $this;
    }

    public function getBillingAddressHouseNumber(): ?string
    {
        return $this->billingAddressHouseNumber;
    }

    public function setBillingAddressHouseNumber(?string $deliveryBillingHouseNumber): OrderResponse
    {
        $this->billingAddressHouseNumber = $deliveryBillingHouseNumber;

        return $this;
    }

    public function getBillingAddressPostalCode(): ?string
    {
        return $this->billingAddressPostalCode;
    }

    public function setBillingAddressPostalCode(?string $deliveryBillingPostalCode): OrderResponse
    {
        $this->billingAddressPostalCode = $deliveryBillingPostalCode;

        return $this;
    }

    public function getBillingAddressCity(): ?string
    {
        return $this->billingAddressCity;
    }

    public function setBillingAddressCity(?string $deliveryBillingCity): OrderResponse
    {
        $this->billingAddressCity = $deliveryBillingCity;

        return $this;
    }

    public function getBillingAddressCountry(): ?string
    {
        return $this->billingAddressCountry;
    }

    public function setBillingAddressCountry(?string $deliveryBillingCountry): OrderResponse
    {
        $this->billingAddressCountry = $deliveryBillingCountry;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): OrderResponse
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDunningStatus(): ? string
    {
        return $this->dunningStatus;
    }

    public function setDunningStatus(?string $dunningStatus): OrderResponse
    {
        $this->dunningStatus = $dunningStatus;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->getExternalCode(), // This is very confusing because the api used to return the external_code as an order_id.
            'uuid' => $this->getUuid(),
            'state' => $this->getState(),
            'reasons' => $this->getReasons() ? join(', ', $this->getReasons()) : null,
            'amount' => $this->getAmountGross(),
            'amount_net' => $this->getAmountNet(),
            'amount_tax' => $this->getAmountTax(),
            'duration' => $this->getDuration(),
            'dunning_status' => $this->getDunningStatus(),
            'debtor_company' => [
                'name' => $this->getCompanyName(),
                'address_house_number' => $this->getCompanyAddressHouseNumber(),
                'address_street' => $this->getCompanyAddressStreet(),
                'address_postal_code' => $this->getCompanyAddressPostalCode(),
                'address_city' => $this->getCompanyAddressCity(),
                'address_country' => $this->getCompanyAddressCountry(),
            ],
            'bank_account' => [
                'iban' => $this->getBankAccountIban(),
                'bic' => $this->getBankAccountBic(),
            ],
            'invoice' => [
                'invoice_number' => $this->getInvoiceNumber(),
                'payout_amount' => $this->getPayoutAmount(),
                'outstanding_amount' => $this->getOutstandingAmount(),
                'fee_amount' => $this->getFeeAmount(),
                'fee_rate' => $this->getFeeRate(),
                'due_date' => $this->getDueDate() ? $this->getDueDate()->format('Y-m-d') : null,
            ],
            'debtor_external_data' => [
                'merchant_customer_id' => $this->getDebtorExternalDataCustomerId(),
                'name' => $this->getDebtorExternalDataCompanyName(),
                'address_country' => $this->getDebtorExternalDataAddressCountry(),
                'address_city' => $this->getDebtorExternalDataAddressCity(),
                'address_postal_code' => $this->getDebtorExternalDataAddressPostalCode(),
                'address_street' => $this->getDebtorExternalDataAddressStreet(),
                'address_house' => $this->getDebtorExternalDataAddressHouse(),
                'industry_sector' => $this->getDebtorExternalDataIndustrySector(),
            ],
            'delivery_address' => [
                'house_number' => $this->getDeliveryAddressHouseNumber(),
                'street' => $this->getDeliveryAddressStreet(),
                'city' => $this->getDeliveryAddressCity(),
                'postal_code' => $this->getDeliveryAddressPostalCode(),
                'country' => $this->getDeliveryAddressCountry(),
            ],
            'billing_address' => [
                'house_number' => $this->getBillingAddressHouseNumber(),
                'street' => $this->getBillingAddressStreet(),
                'city' => $this->getBillingAddressCity(),
                'postal_code' => $this->getBillingAddressPostalCode(),
                'country' => $this->getBillingAddressCountry(),
            ],
            'created_at' => $this->getCreatedAt()->format(\DateTime::ISO8601),
            'shipped_at' => ($this->getShippedAt() ? $this->getShippedAt()->format(\DateTime::ISO8601) : null),
        ];
    }
}
