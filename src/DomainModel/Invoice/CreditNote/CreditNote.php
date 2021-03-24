<?php

declare(strict_types=1);

namespace App\DomainModel\Invoice\CreditNote;

use Ozean12\Money\TaxedMoney\TaxedMoney;

final class CreditNote
{
    public const EXTERNAL_CODE_SUFFIX = '-CN';

    private string $uuid;

    private TaxedMoney $amount;

    private ?string $externalCode;

    private ?string $externalComment;

    private ?string $internalComment;

    private ?string $invoiceUuid;

    private \DateTime $createdAt;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): CreditNote
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getAmount(): TaxedMoney
    {
        return $this->amount;
    }

    public function setAmount(TaxedMoney $amount): CreditNote
    {
        $this->amount = $amount;

        return $this;
    }

    public function getExternalCode(): ?string
    {
        return $this->externalCode;
    }

    public function setExternalCode(?string $code): CreditNote
    {
        $this->externalCode = $code;

        return $this;
    }

    public function getInternalComment(): ?string
    {
        return $this->internalComment;
    }

    public function setInternalComment(?string $internalComment): CreditNote
    {
        $this->internalComment = $internalComment;

        return $this;
    }

    public function getExternalComment(): ?string
    {
        return $this->externalComment;
    }

    public function setExternalComment(?string $externalComment): CreditNote
    {
        $this->externalComment = $externalComment;

        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt): CreditNote
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getInvoiceUuid(): ?string
    {
        return $this->invoiceUuid;
    }

    public function setInvoiceUuid(?string $invoiceUuid): CreditNote
    {
        $this->invoiceUuid = $invoiceUuid;

        return $this;
    }
}
