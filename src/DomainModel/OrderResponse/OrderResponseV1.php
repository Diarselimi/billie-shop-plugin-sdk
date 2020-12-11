<?php

namespace App\DomainModel\OrderResponse;

use Ozean12\Money\TaxedMoney\TaxedMoney;
use App\Support\DateFormat;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="OrderResponseV1", title="Order Entity", type="object", properties={
 *      @OA\Property(property="order_id", type="string", nullable=true, example="C-10123456789-0001"),
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="state", ref="#/components/schemas/OrderState", example="created"),
 *      @OA\Property(property="reasons", enum=\App\DomainModel\Order\OrderDeclinedReasonsMapper::REASONS, type="string", nullable=true, deprecated=true),
 *      @OA\Property(property="decline_reason", ref="#/components/schemas/OrderDeclineReason", nullable=true),
 *      @OA\Property(property="amount", type="number", format="float", nullable=false, example=123.57, description="Gross amount"),
 *      @OA\Property(property="amount_net", type="number", format="float", nullable=false, example=100.12),
 *      @OA\Property(property="amount_tax", type="number", format="float", nullable=false, example=23.45),
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
 *          @OA\Property(property="pending_merchant_payment_amount", type="number", format="float", nullable=true),
 *          @OA\Property(property="pending_cancellation_amount", type="number", format="float", nullable=true),
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
 *      @OA\Property(property="debtor_uuid", ref="#/components/schemas/UUID"),
 * })
 */
class OrderResponseV1 extends AbstractOrderResponse
{
    private $invoiceNumber;

    private $payoutAmount;

    private $outstandingAmount;

    private $amount;

    private $feeAmount;

    private $feeRate;

    private $dueDate;

    private $pendingMerchantPaymentAmount;

    private $pendingCancellationAmount;

    /**
     * @deprecated use declineReason
     */
    private $reasons;

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
    public function setReasons(array $reasons): OrderResponseV1
    {
        $this->reasons = $reasons;

        return $this;
    }

    public function getInvoiceNumber(): ? string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): OrderResponseV1
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getPayoutAmount(): ? float
    {
        return $this->payoutAmount;
    }

    public function setPayoutAmount(float $payoutAmount): OrderResponseV1
    {
        $this->payoutAmount = $payoutAmount;

        return $this;
    }

    public function getOutstandingAmount(): ? float
    {
        return $this->outstandingAmount;
    }

    public function setOutstandingAmount(float $outstandingAmount): OrderResponseV1
    {
        $this->outstandingAmount = $outstandingAmount;

        return $this;
    }

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(TaxedMoney $amount): OrderResponseV1
    {
        $this->amount = $amount;

        return $this;
    }

    public function getFeeAmount(): ? float
    {
        return $this->feeAmount;
    }

    public function setFeeAmount(float $feeAmount): OrderResponseV1
    {
        $this->feeAmount = $feeAmount;

        return $this;
    }

    public function getFeeRate(): ? float
    {
        return $this->feeRate;
    }

    public function setFeeRate(float $feeRate): OrderResponseV1
    {
        $this->feeRate = $feeRate;

        return $this;
    }

    public function getDueDate(): ? \DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): OrderResponseV1
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getPendingMerchantPaymentAmount(): ?float
    {
        return $this->pendingMerchantPaymentAmount;
    }

    public function setPendingMerchantPaymentAmount(?float $pendingMerchantPaymentAmount): OrderResponseV1
    {
        $this->pendingMerchantPaymentAmount = $pendingMerchantPaymentAmount;

        return $this;
    }

    public function getPendingCancellationAmount(): ?float
    {
        return $this->pendingCancellationAmount;
    }

    public function setPendingCancellationAmount(?float $pendingCancellationAmount): OrderResponseV1
    {
        $this->pendingCancellationAmount = $pendingCancellationAmount;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'reasons' => $this->getReasons() ? implode(', ', $this->getReasons()) : null,
            'invoice' => [
                'invoice_number' => $this->getInvoiceNumber(),
                'payout_amount' => $this->getPayoutAmount(),
                'outstanding_amount' => $this->getOutstandingAmount(),
                'fee_amount' => $this->getFeeAmount(),
                'fee_rate' => $this->getFeeRate(),
                'due_date' => $this->getDueDate() ? $this->getDueDate()->format(DateFormat::FORMAT_YMD) : null,
                'pending_merchant_payment_amount' => $this->getPendingMerchantPaymentAmount(),
                'pending_cancellation_amount' => $this->getPendingCancellationAmount(),
            ],
        ]);
    }
}
