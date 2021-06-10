<?php

declare(strict_types=1);

namespace App\DomainModel\Payment;

use App\DomainModel\ArrayableInterface;
use App\Support\DateFormat;
use OpenApi\Annotations as OA;
use Ozean12\Money\Money;

/**
 * @OA\Schema(schema="BankTransactionDTO", type="object", properties={
 *      @OA\Property(property="transaction_uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="state", type="string"),
 *      @OA\Property(property="type", type="string"),
 *      @OA\Property(property="amount", type="number", format="float"),
 *      @OA\Property(property="debtor_name", type="string"),
 *      @OA\Property(property="created_at", ref="#/components/schemas/DateTime"),
 * })
 */
final class BankTransaction implements ArrayableInterface
{
    public const TYPE_MERCHANT_PAYMENT = 'merchant_payment';

    /**
     * aka. Debtor Payment
     */
    public const TYPE_INVOICE_PAYBACK = 'invoice_payback';

    /**
     * aka. Invoice Reduction
     */
    public const TYPE_INVOICE_CANCELLATION = 'invoice_cancellation';

    public const STATE_NEW = 'new';

    public const STATE_COMPLETE = 'complete';

    private ?string $transactionUuid;

    private string $type;

    private string $state;

    private Money $amount;

    private ?string $debtorName;

    private \DateTime $createdAt;

    public function getTransactionUuid(): ?string
    {
        return $this->transactionUuid;
    }

    public function setTransactionUuid(?string $transactionUuid): self
    {
        $this->transactionUuid = $transactionUuid;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function setAmount(Money $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDebtorName(): ?string
    {
        return $this->debtorName;
    }

    public function setDebtorName(?string $debtorName): self
    {
        $this->debtorName = $debtorName;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'transaction_uuid' => $this->getTransactionUuid(),
            'type' => $this->getType(),
            'state' => $this->getState(),
            'amount' => $this->getAmount()->getMoneyValue(),
            'debtor_name' => $this->getDebtorName(),
            'created_at' => $this->getCreatedAt()->format(DateFormat::FORMAT_YMD_HIS),
        ];
    }
}
