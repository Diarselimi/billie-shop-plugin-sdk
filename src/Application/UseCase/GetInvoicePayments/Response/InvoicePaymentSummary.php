<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoicePayments\Response;

use App\DomainModel\ArrayableInterface;
use Ozean12\Money\Money;

/**
 * @OA\Schema(schema="InvoicePaymentSummaryDTO", title="Invoice Payments Summary object", type="object", properties={
 *      @OA\Property(property="merchant_paid_amount", type="number", format="float"),
 *      @OA\Property(property="debtor_paid_amount", type="number", format="float"),
 *      @OA\Property(property="merchant_unmapped_amount", type="number", format="float"),
 *      @OA\Property(property="debtor_unmapped_amount", type="number", format="float"),
 *      @OA\Property(property="total_paid_amount", type="number", format="float"),
 *      @OA\Property(property="cancelled_amount", type="number", format="float"),
 *      @OA\Property(property="open_amount", type="number", format="float"),
 * })
 */
final class InvoicePaymentSummary implements ArrayableInterface
{
    private Money $merchantPaidAmount;

    private Money $debtorPaidAmount;

    private Money $merchantUnmappedAmount;

    private Money $debtorUnmappedAmount;

    private Money $totalPaidAmount;

    private Money $cancelledAmount;

    private Money $openAmount;

    public function __construct()
    {
        $this->merchantPaidAmount = new Money(0.0);
        $this->debtorPaidAmount = new Money(0.0);
        $this->merchantUnmappedAmount = new Money(0.0);
        $this->debtorUnmappedAmount = new Money(0.0);
        $this->totalPaidAmount = new Money(0.0);
        $this->cancelledAmount = new Money(0.0);
        $this->openAmount = new Money(0.0);
    }

    public function getMerchantPaidAmount(): Money
    {
        return $this->merchantPaidAmount;
    }

    public function setMerchantPaidAmount(Money $merchantPaidAmount): InvoicePaymentSummary
    {
        $this->merchantPaidAmount = $merchantPaidAmount;

        return $this;
    }

    public function getDebtorPaidAmount(): Money
    {
        return $this->debtorPaidAmount;
    }

    public function setDebtorPaidAmount(Money $debtorPaidAmount): InvoicePaymentSummary
    {
        $this->debtorPaidAmount = $debtorPaidAmount;

        return $this;
    }

    public function getMerchantUnmappedAmount(): Money
    {
        return $this->merchantUnmappedAmount;
    }

    public function setMerchantUnmappedAmount(Money $merchantUnmappedAmount): InvoicePaymentSummary
    {
        $this->merchantUnmappedAmount = $merchantUnmappedAmount;

        return $this;
    }

    public function getDebtorUnmappedAmount(): Money
    {
        return $this->debtorUnmappedAmount;
    }

    public function setDebtorUnmappedAmount(Money $debtorUnmappedAmount): InvoicePaymentSummary
    {
        $this->debtorUnmappedAmount = $debtorUnmappedAmount;

        return $this;
    }

    public function getTotalPaidAmount(): Money
    {
        return $this->totalPaidAmount;
    }

    public function setTotalPaidAmount(Money $totalPaidAmount): InvoicePaymentSummary
    {
        $this->totalPaidAmount = $totalPaidAmount;

        return $this;
    }

    public function getOpenAmount(): Money
    {
        return $this->openAmount;
    }

    public function setOpenAmount(Money $openAmount): InvoicePaymentSummary
    {
        $this->openAmount = $openAmount;

        return $this;
    }

    public function getCancelledAmount(): Money
    {
        return $this->cancelledAmount;
    }

    public function setCancelledAmount(Money $cancelledAmount): InvoicePaymentSummary
    {
        $this->cancelledAmount = $cancelledAmount;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'merchant_paid_amount' => $this->getMerchantPaidAmount()->getMoneyValue(),
            'debtor_paid_amount' => $this->getDebtorPaidAmount()->getMoneyValue(),
            'merchant_unmapped_amount' => $this->getMerchantUnmappedAmount()->getMoneyValue(),
            'debtor_unmapped_amount' => $this->getDebtorUnmappedAmount()->getMoneyValue(),
            'total_paid_amount' => $this->getTotalPaidAmount()->getMoneyValue(),
            'cancelled_amount' => $this->getCancelledAmount()->getMoneyValue(),
            'open_amount' => $this->getOpenAmount()->getMoneyValue(),
        ];
    }
}
