<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetInvoicePayments\Response;

use App\DomainModel\ArrayableInterface;
use Ozean12\Money\Money;

/**
 * @OA\Schema(schema="InvoicePaymentSummaryDTO", title="Invoice Payments Summary object", type="object", properties={
 *      @OA\Property(property="pending_cancellation_amount", type="number", format="float"),
 *      @OA\Property(property="merchant_payment_amount", type="number", format="float"),
 *      @OA\Property(property="debtor_payment_amount", type="number", format="float"),
 *      @OA\Property(property="pending_merchant_payment_amount", type="number", format="float"),
 *      @OA\Property(property="total_payment_amount", type="number", format="float"),
 *      @OA\Property(property="cancellation_amount", type="number", format="float"),
 *      @OA\Property(property="deductible_amount", type="number", format="float"),
 * })
 */
final class InvoicePaymentSummary implements ArrayableInterface
{
    private Money $pendingCancellationAmount;

    private Money $merchantPaymentAmount;

    private Money $debtorPaymentAmount;

    private Money $pendingMerchantPaymentAmount;

    private Money $totalPaymentAmount;

    private Money $cancellationAmount;

    private Money $deductibleAmount;

    public function __construct()
    {
        $this->pendingCancellationAmount = new Money(0.0);
        $this->merchantPaymentAmount = new Money(0.0);
        $this->debtorPaymentAmount = new Money(0.0);
        $this->pendingMerchantPaymentAmount = new Money(0.0);
        $this->totalPaymentAmount = new Money(0.0);
        $this->cancellationAmount = new Money(0.0);
        $this->deductibleAmount = new Money(0.0);
    }

    public function getPendingCancellationAmount(): Money
    {
        return $this->pendingCancellationAmount;
    }

    public function setPendingCancellationAmount(Money $pendingCancellationAmount): InvoicePaymentSummary
    {
        $this->pendingCancellationAmount = $pendingCancellationAmount;

        return $this;
    }

    public function getMerchantPaymentAmount(): Money
    {
        return $this->merchantPaymentAmount;
    }

    public function setMerchantPaymentAmount(Money $merchantPaymentAmount): InvoicePaymentSummary
    {
        $this->merchantPaymentAmount = $merchantPaymentAmount;

        return $this;
    }

    public function getDebtorPaymentAmount(): Money
    {
        return $this->debtorPaymentAmount;
    }

    public function setDebtorPaymentAmount(Money $debtorPaymentAmount): InvoicePaymentSummary
    {
        $this->debtorPaymentAmount = $debtorPaymentAmount;

        return $this;
    }

    public function getPendingMerchantPaymentAmount(): Money
    {
        return $this->pendingMerchantPaymentAmount;
    }

    public function setPendingMerchantPaymentAmount(Money $pendingMerchantPaymentAmount): InvoicePaymentSummary
    {
        $this->pendingMerchantPaymentAmount = $pendingMerchantPaymentAmount;

        return $this;
    }

    public function getTotalPaymentAmount(): Money
    {
        return $this->totalPaymentAmount;
    }

    public function setTotalPaymentAmount(Money $totalPaymentAmount): InvoicePaymentSummary
    {
        $this->totalPaymentAmount = $totalPaymentAmount;

        return $this;
    }

    public function getCancellationAmount(): Money
    {
        return $this->cancellationAmount;
    }

    public function setCancellationAmount(Money $cancellationAmount): InvoicePaymentSummary
    {
        $this->cancellationAmount = $cancellationAmount;

        return $this;
    }

    public function getDeductibleAmount(): Money
    {
        return $this->deductibleAmount;
    }

    public function setDeductibleAmount(Money $deductibleAmount): InvoicePaymentSummary
    {
        $this->deductibleAmount = $deductibleAmount;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'pending_cancellation_amount' => $this->getPendingCancellationAmount()->getMoneyValue(),
            'merchant_payment_amount' => $this->getMerchantPaymentAmount()->getMoneyValue(),
            'debtor_payment_amount' => $this->getDebtorPaymentAmount()->getMoneyValue(),
            'pending_merchant_payment_amount' => $this->getPendingMerchantPaymentAmount()->getMoneyValue(),
            'total_payment_amount' => $this->getTotalPaymentAmount()->getMoneyValue(),
            'cancellation_amount' => $this->getCancellationAmount()->getMoneyValue(),
            'deductible_amount' => $this->getDeductibleAmount()->getMoneyValue(),
        ];
    }
}
