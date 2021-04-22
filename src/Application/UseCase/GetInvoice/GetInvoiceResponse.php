<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoice;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\Invoice\Invoice;
use App\Support\DateFormat;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetInvoiceResponse", title="Get Invoice Response", type="object", properties={
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText", nullable=true),
 *      @OA\Property(property="duration", type="number", nullable=false),
 *      @OA\Property(property="payout_amount", type="number", format="float", nullable=false),
 *      @OA\Property(property="amount", type="number", format="float", nullable=false),
 *      @OA\Property(property="amount_net", type="number", format="float", nullable=false),
 *      @OA\Property(property="amount_tax", type="number", format="float", nullable=false),
 *      @OA\Property(property="outstanding_amount", type="number", format="float", nullable=false),
 *      @OA\Property(property="fee_amount", type="number", format="float", nullable=false),
 *      @OA\Property(property="fee_rate", type="number", format="float", nullable=false),
 *      @OA\Property(property="created_at", type="string", format="date", nullable=false, example="2019-03-20"),
 *      @OA\Property(property="due_date", type="string", format="date", nullable=false, example="2019-03-20"),
 *      @OA\Property(property="pending_merchant_payment_amount", type="number", format="float", nullable=false),
 *      @OA\Property(property="pending_cancellation_amount", type="number", format="float", nullable=false),
 *      @OA\Property(property="state", type="string", nullable=true, example="created"),
 *      @OA\Property(property="orders", type="array", @OA\Items(type="object", properties={
 *          @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *          @OA\Property(property="external_code", type="string", nullable=true, example="C-10123456789-0001"),
 *          @OA\Property(property="amount", type="number", format="float", nullable=false),
 *          @OA\Property(property="amount_net", type="number", format="float", nullable=false),
 *          @OA\Property(property="amount_tax", type="number", format="float", nullable=false),
 *     }))
 * })
 */
class GetInvoiceResponse implements ArrayableInterface
{
    private string $uuid;

    private string $invoiceNumber;

    private int $duration;

    private float $payoutAmount;

    private float $amount;

    private float $amountNet;

    private float $amountTax;

    private float $outstandingAmount;

    private float $pendingMerchantPaymentAmount;

    private float $pendingCancellationAmount;

    private float $feeAmount;

    private float $feeRate;

    private \DateTime $createdAt;

    private \DateTime $dueDate;

    private ?string $state;

    private array

 $ordersResponse;

    private array

 $creditNotesResponse;

    public function __construct(Invoice $invoice, array $ordersResponse, array $creditNotesResponse)
    {
        $this->invoiceNumber = $invoice->getExternalCode();
        $this->duration = $invoice->getDuration();
        $this->payoutAmount = $invoice->getPayoutAmount()->getMoneyValue();
        $this->amount = $invoice->getAmount()->getGross()->getMoneyValue();
        $this->amountNet = $invoice->getAmount()->getNet()->getMoneyValue();
        $this->amountTax = $invoice->getAmount()->getTax()->getMoneyValue();
        $this->outstandingAmount = $invoice->getOutstandingAmount()->getMoneyValue();
        $this->pendingMerchantPaymentAmount = $invoice->getMerchantPendingPaymentAmount()->getMoneyValue();
        $this->pendingCancellationAmount = $invoice->getInvoicePendingCancellationAmount()->getMoneyValue();
        $this->feeAmount = $invoice->getFeeAmount()->getGross()->getMoneyValue();
        $this->feeRate = $invoice->getFeeRate()->toBase100();
        $this->createdAt = $invoice->getCreatedAt();
        $this->dueDate = $invoice->getDueDate();
        $this->state = $invoice->getState();
        $this->ordersResponse = $ordersResponse;
        $this->creditNotesResponse = $creditNotesResponse;
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

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getPayoutAmount(): float
    {
        return $this->payoutAmount;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getAmountNet(): float
    {
        return $this->amountNet;
    }

    public function getAmountTax(): float
    {
        return $this->amountTax;
    }

    public function getOutstandingAmount(): float
    {
        return $this->outstandingAmount;
    }

    public function getPendingMerchantPaymentAmount(): float
    {
        return $this->pendingMerchantPaymentAmount;
    }

    public function getPendingCancellationAmount(): float
    {
        return $this->pendingCancellationAmount;
    }

    public function getFeeAmount(): float
    {
        return $this->feeAmount;
    }

    public function getFeeRate(): float
    {
        return $this->feeRate;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getDueDate(): \DateTime
    {
        return $this->dueDate;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->getUuid(),
            'invoice_number' => $this->getInvoiceNumber(),
            'duration' => $this->getDuration(),
            'payout_amount' => $this->getPayoutAmount(),
            'amount' => $this->getAmount(),
            'amount_net' => $this->getAmountNet(),
            'amount_tax' => $this->getAmountTax(),
            'outstanding_amount' => $this->getOutstandingAmount(),
            'fee_amount' => $this->getFeeAmount(),
            'fee_rate' => $this->getFeeRate(),
            'created_at' => $this->getDueDate() ? $this->getCreatedAt()->format(DateFormat::FORMAT_YMD) : null,
            'due_date' => $this->getDueDate() ? $this->getDueDate()->format(DateFormat::FORMAT_YMD) : null,
            'pending_merchant_payment_amount' => $this->getPendingMerchantPaymentAmount(),
            'pending_cancellation_amount' => $this->getPendingCancellationAmount(),
            'state' => $this->getState(),
            'orders' => $this->ordersResponse,
            'credit_notes' => $this->creditNotesResponse,
        ];
    }
}
