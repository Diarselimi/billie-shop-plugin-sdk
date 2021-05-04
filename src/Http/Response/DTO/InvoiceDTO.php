<?php

namespace App\Http\Response\DTO;

use App\DomainModel\Invoice\Invoice as DomainInvoice;
use App\DomainModel\ArrayableInterface;
use App\Support\DateFormat;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="Invoice", title="Invoice", type="object", properties={
 *      @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="invoice_number", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="state", ref="#/components/schemas/TinyText"),
 *      @OA\Property(property="payout_amount", type="number", format="float"),
 *      @OA\Property(property="amount", type="number", format="float"),
 *      @OA\Property(property="amount_net", type="number", format="float"),
 *      @OA\Property(property="amount_tax", type="number", format="float"),
 *      @OA\Property(property="outstanding_amount", type="number", format="float"),
 *      @OA\Property(property="pending_merchant_payment_amount", type="number", format="float"),
 *      @OA\Property(property="pending_cancellation_amount", type="number", format="float"),
 *      @OA\Property(property="fee_amount", type="number", format="float"),
 *      @OA\Property(property="fee_rate", type="number", format="float"),
 *      @OA\Property(property="due_date", ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="created_at", ref="#/components/schemas/DateTime"),
 * })
 */
class InvoiceDTO implements ArrayableInterface
{
    private DomainInvoice $invoice;

    public function __construct(DomainInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->invoice->getUuid(),
            'invoice_number' => $this->invoice->getExternalCode(),
            'payout_amount' => $this->invoice->getPayoutAmount()->getMoneyValue(),
            'outstanding_amount' => $this->invoice->getOutstandingAmount()->getMoneyValue(),
            'amount' => (new TaxedMoneyDTO($this->invoice->getAmount()))->toArray(),
            'fee_amount' => $this->invoice->getFeeAmount()->getGross()->getMoneyValue(),
            'fee_rate' => $this->invoice->getFeeRate()->toBase100(),
            'due_date' => $this->invoice->getDueDate() ? $this->invoice->getDueDate()->format(DateFormat::FORMAT_YMD) : null,
            'created_at' => $this->invoice->getDueDate() ? $this->invoice->getCreatedAt()->format(DateFormat::FORMAT_YMD) : null,
            'duration' => $this->invoice->getDuration(),
            'state' => $this->invoice->getState(),
            'pending_merchant_payment_amount' => $this->invoice->getMerchantPendingPaymentAmount()->getMoneyValue(),
            'pending_cancellation_amount' => $this->invoice->getInvoicePendingCancellationAmount()->getMoneyValue(),
        ];
    }
}
