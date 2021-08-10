<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoice;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\PaymentMethod\PaymentMethodCollection;
use App\Http\Response\DTO\PaymentMethodDTO;
use App\Http\Response\DTO\TaxedMoneyDTO;
use OpenApi\Annotations as OA;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ozean12\Support\Formatting\DateFormat;

/**
 * @OA\Schema(schema="GetInvoiceResponse", title="Get Invoice Response", type="object", properties={
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID", description="Unique identifier of the invoice object."),
 *      @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText", nullable=true, description="Customer facing merchant defined invoice number which must be unique."),
 *      @OA\Property(property="duration", type="number", nullable=false, description="The defined time allowed for payment. Measured in days"),
 *      @OA\Property(property="payout_amount", type="number", format="float", nullable=false, description="The amount that will be paid out to the merchant."),
 *      @OA\Property(property="amount", type="number", ref="#/components/schemas/AmountDTO", nullable=false, description="Total invoice amount including gross amount, net amount and tax amount."),
 *      @OA\Property(property="outstanding_amount", type="number", format="float", nullable=false, description="The remaining amount that has to be paid by the debtor still."),
 *      @OA\Property(property="fee_amount", type="number", format="float", nullable=false, description="The fee amount per invoice paid by the merchant to Billie."),
 *      @OA\Property(property="fee_rate", type="number", format="float", nullable=false, description="The fee rate is the percentage that the merchant pays to Billie."),
 *      @OA\Property(property="created_at", type="string", format="date", nullable=false, example="2019-03-20", description="The date and time when the invoice was created."),
 *      @OA\Property(property="due_date", type="string", format="date", nullable=false, example="2019-03-20", description="The date when this invoice is due to be paid back."),
 *      @OA\Property(property="state", type="string", nullable=true, example="created", description="The state of the invoice."),
 *      @OA\Property(property="payment_methods", ref="#/components/schemas/PaymentMethodCollection")
 * })
 */
class GetInvoiceResponse implements ArrayableInterface
{
    private string $uuid;

    private string $invoiceNumber;

    private int $duration;

    private float $payoutAmount;

    private ?TaxedMoney $amount;

    private float $outstandingAmount;

    private float $pendingMerchantPaymentAmount;

    private float $pendingCancellationAmount;

    private float $feeAmount;

    private float $feeRate;

    private \DateTime $createdAt;

    private \DateTime $dueDate;

    private ?string $state;

    private array $ordersResponse;

    private array $creditNotesResponse;

    private PaymentMethodCollection $paymentMethods;

    public function __construct(
        Invoice $invoice,
        array $ordersResponse,
        array $creditNotesResponse,
        PaymentMethodCollection $paymentMethods
    ) {
        $this->invoiceNumber = $invoice->getExternalCode();
        $this->duration = $invoice->getDuration();
        $this->payoutAmount = $invoice->getPayoutAmount()->getMoneyValue();
        $this->amount = $invoice->getAmount();
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
        $this->paymentMethods = $paymentMethods;
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

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
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
            'amount' => (new TaxedMoneyDTO($this->getAmount()))->toArray(),
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
            'payment_methods' => PaymentMethodDTO::collectionToArray($this->paymentMethods),
        ];
    }
}
