<?php

declare(strict_types=1);

namespace App\DomainModel\PaymentMethod;

use Ozean12\Sepa\Client\DomainModel\Mandate\SepaMandate;
use Ozean12\Support\ValueObject\BankAccount;

class PaymentMethod
{
    public const TYPE_BANK_TRANSFER = 'bank_transfer';

    public const TYPE_DIRECT_DEBIT = 'direct_debit';

    private string $type;

    private BankAccount $bankAccount;

    private ?SepaMandate $sepaMandate;

    private ?\DateTimeInterface $sepaMandateExecutionDate;

    public function __construct(
        string $type,
        BankAccount $bank,
        ?SepaMandate $sepaMandate = null,
        ?\DateTimeInterface $sepaMandateExecutionDate = null
    ) {
        $this->type = $type;
        $this->bankAccount = $bank;
        $this->sepaMandate = $sepaMandate;
        $this->sepaMandateExecutionDate = $sepaMandateExecutionDate;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getBankAccount(): BankAccount
    {
        return $this->bankAccount;
    }

    public function getSepaMandate(): ?SepaMandate
    {
        return $this->sepaMandate;
    }

    public function hasMandate(): bool
    {
        return $this->sepaMandate !== null;
    }

    public function getSepaMandateExecutionDate(): ?\DateTimeInterface
    {
        return $this->sepaMandateExecutionDate;
    }

    public function hasSepaMandateExecutionDate(): bool
    {
        return $this->sepaMandateExecutionDate !== null;
    }
}
