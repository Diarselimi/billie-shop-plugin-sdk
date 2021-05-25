<?php

namespace App\Application\UseCase\OrderOutstandingAmountChange;

use Ozean12\Money\Money;

class OrderOutstandingAmountChangeRequest
{
    public const TYPE_CANCELLATION = 'cancelation';

    public const TYPE_PAYMENT = 'payment';

    private string $invoiceUuid;

    private string $type;

    private Money $amountChange;

    private Money $outstandingAmount;

    private Money $paidAmount;

    private ?string $iban;

    private ?string $accountHolder;

    public function __construct(
        string $invoiceUuid,
        string $type,
        Money $amountChange,
        Money $outstandingAmount,
        Money $paidAmount,
        ?string $iban,
        ?string $accountHolder
    ) {
        $this->invoiceUuid = $invoiceUuid;
        $this->type = $type;
        $this->amountChange = $amountChange;
        $this->outstandingAmount = $outstandingAmount;
        $this->paidAmount = $paidAmount;
        $this->iban = $iban;
        $this->accountHolder = $accountHolder;
    }

    public function getInvoiceUuid(): string
    {
        return $this->invoiceUuid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmountChange(): Money
    {
        return $this->amountChange;
    }

    public function getOutstandingAmount(): Money
    {
        return $this->outstandingAmount;
    }

    public function getPaidAmount(): Money
    {
        return $this->paidAmount;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function getAccountHolder(): ?string
    {
        return $this->accountHolder;
    }
}
