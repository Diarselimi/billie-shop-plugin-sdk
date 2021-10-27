<?php

declare(strict_types=1);

namespace App\DomainModel\Payment;

use Ozean12\Money\Money;
use Ramsey\Uuid\UuidInterface;

final class BankTransactionDetails
{
    private UuidInterface $uuid;

    private Money $amount;

    private Money $overPaidAmount;

    private bool $isAllocated;

    private BankTransactionDetailsOrderCollection $orders;

    private ?UuidInterface $merchantDebtorUuid;

    private ?string $counterpartyIban;

    private ?string $counterpartyName;

    private ?\DateTimeInterface $transactionDate;

    private ?string $transactionReference;

    private array $invoicePayments;

    public function __construct(
        UuidInterface $uuid,
        Money $amount,
        Money $overPaidAmount,
        bool $isAllocated,
        BankTransactionDetailsOrderCollection $orders,
        ?UuidInterface $merchantDebtorUuid = null,
        ?string $counterpartyIban = null,
        ?string $counterpartyName = null,
        ?\DateTimeInterface $transactionDate = null,
        ?string $transactionReference = null
    ) {
        $this->uuid = $uuid;
        $this->amount = $amount;
        $this->overPaidAmount = $overPaidAmount;
        $this->isAllocated = $isAllocated;
        $this->orders = $orders;
        $this->merchantDebtorUuid = $merchantDebtorUuid;
        $this->counterpartyIban = $counterpartyIban;
        $this->counterpartyName = $counterpartyName;
        $this->transactionDate = $transactionDate;
        $this->transactionReference = $transactionReference;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getOverPaidAmount(): Money
    {
        return $this->overPaidAmount;
    }

    public function isAllocated(): bool
    {
        return $this->isAllocated;
    }

    public function getOrders(): BankTransactionDetailsOrderCollection
    {
        return $this->orders;
    }

    public function getMerchantDebtorUuid(): ?UuidInterface
    {
        return $this->merchantDebtorUuid;
    }

    public function getCounterpartyIban(): ?string
    {
        return $this->counterpartyIban;
    }

    public function getCounterpartyName(): ?string
    {
        return $this->counterpartyName;
    }

    public function getTransactionDate(): ?\DateTimeInterface
    {
        return $this->transactionDate;
    }

    public function getTransactionReference(): ?string
    {
        return $this->transactionReference;
    }

    public function addInvoicePayments(array $invoicePayments)
    {
        $this->invoicePayments = $invoicePayments;
    }
}
