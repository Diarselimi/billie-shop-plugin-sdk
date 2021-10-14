<?php

declare(strict_types=1);

namespace App\DomainModel\Payment;

use Ozean12\Money\Money;
use Ramsey\Uuid\UuidInterface;

final class BankTransactionDetailsOrder
{
    private UuidInterface $uuid;

    private Money $amount;

    private Money $mappedAmount;

    private Money $outstandingAmount;

    private ?string $externalId;

    private ?string $invoiceNumber;

    public function __construct(
        UuidInterface $uuid,
        Money $amount,
        Money $mappedAmount,
        Money $outstandingAmount,
        ?string $externalId = null,
        ?string $invoiceNumber = null
    ) {
        $this->uuid = $uuid;
        $this->amount = $amount;
        $this->mappedAmount = $mappedAmount;
        $this->outstandingAmount = $outstandingAmount;
        $this->externalId = $externalId;
        $this->invoiceNumber = $invoiceNumber;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getMappedAmount(): Money
    {
        return $this->mappedAmount;
    }

    public function getOutstandingAmount(): Money
    {
        return $this->outstandingAmount;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }
}
