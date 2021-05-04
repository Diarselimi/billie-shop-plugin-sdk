<?php

namespace App\DomainModel\OrderResponse;

use Ozean12\Money\TaxedMoney\TaxedMoney;
use App\DomainModel\ArrayableInterface;
use App\Support\DateFormat;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="OrderInvoiceResponse", title="Order Entity", type="object", properties={
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID", nullable=true),
 *      @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText", nullable=true),
 *      @OA\Property(property="state", ref="#/components/schemas/TinyText", nullable=true),
 *      @OA\Property(property="payout_amount", type="number", format="float", nullable=true),
 *      @OA\Property(property="amount", type="number", format="float"),
 *      @OA\Property(property="amount_net", type="number", format="float"),
 *      @OA\Property(property="amount_tax", type="number", format="float"),
 *      @OA\Property(property="outstanding_amount", type="number", format="float", nullable=true),
 *      @OA\Property(property="pending_merchant_payment_amount", type="number", format="float", nullable=true),
 *      @OA\Property(property="pending_cancellation_amount", type="number", format="float", nullable=true),
 *      @OA\Property(property="fee_amount", type="number", format="float", nullable=true),
 *      @OA\Property(property="fee_rate", type="number", format="float", nullable=true),
 *      @OA\Property(property="due_date", type="string", format="date", example="2019-03-20"),
 *      @OA\Property(property="created_at", type="string", format="date", example="2019-03-20"),
 * })
 */
class LegacyOrderInvoiceResponse implements ArrayableInterface
{
    private $uuid;

    private $invoiceNumber;

    private $payoutAmount;

    private $outstandingAmount;

    private $amount;

    private $feeAmount;

    private $feeRate;

    private $dueDate;

    private $createdAt;

    private $duration;

    private $pendingMerchantPaymentAmount;

    private $pendingCancellationAmount;

    private $state;

    public function getInvoiceNumber(): ? string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getPayoutAmount(): ? float
    {
        return $this->payoutAmount;
    }

    public function setPayoutAmount(float $payoutAmount): self
    {
        $this->payoutAmount = $payoutAmount;

        return $this;
    }

    public function getOutstandingAmount(): ? float
    {
        return $this->outstandingAmount;
    }

    public function setOutstandingAmount(float $outstandingAmount): self
    {
        $this->outstandingAmount = $outstandingAmount;

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

    public function getFeeAmount(): ? float
    {
        return $this->feeAmount;
    }

    public function setFeeAmount(float $feeAmount): self
    {
        $this->feeAmount = $feeAmount;

        return $this;
    }

    public function getFeeRate(): ? float
    {
        return $this->feeRate;
    }

    public function setFeeRate(float $feeRate): self
    {
        $this->feeRate = $feeRate;

        return $this;
    }

    public function getDueDate(): ? \DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): self
    {
        $this->dueDate = $dueDate;

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

    public function getPendingMerchantPaymentAmount(): ?float
    {
        return $this->pendingMerchantPaymentAmount;
    }

    public function setPendingMerchantPaymentAmount(?float $pendingMerchantPaymentAmount): self
    {
        $this->pendingMerchantPaymentAmount = $pendingMerchantPaymentAmount;

        return $this;
    }

    public function getPendingCancellationAmount(): ?float
    {
        return $this->pendingCancellationAmount;
    }

    public function setPendingCancellationAmount(?float $pendingCancellationAmount): self
    {
        $this->pendingCancellationAmount = $pendingCancellationAmount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): LegacyOrderInvoiceResponse
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): LegacyOrderInvoiceResponse
    {
        $this->state = $state;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): LegacyOrderInvoiceResponse
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->getUuid(),
            'invoice_number' => $this->getInvoiceNumber(),
            'payout_amount' => $this->getPayoutAmount(),
            'outstanding_amount' => $this->getOutstandingAmount(),
            'amount' => $this->getAmount()->getGross()->toFloat(),
            'amount_net' => $this->getAmount()->getNet()->toFloat(),
            'amount_tax' => $this->getAmount()->getTax()->toFloat(),
            'fee_amount' => $this->getFeeAmount(),
            'fee_rate' => $this->getFeeRate(),
            'due_date' => $this->getDueDate() ? $this->getDueDate()->format(DateFormat::FORMAT_YMD) : null,
            'created_at' => $this->getDueDate() ? $this->getCreatedAt()->format(DateFormat::FORMAT_YMD) : null,
            'duration' => $this->getDuration(),
            'state' => $this->getState(),
            'pending_merchant_payment_amount' => $this->getPendingMerchantPaymentAmount(),
            'pending_cancellation_amount' => $this->getPendingCancellationAmount(),
        ];
    }
}
