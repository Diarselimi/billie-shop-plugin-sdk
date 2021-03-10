<?php

namespace App\DomainModel\OrderPayments;

use App\DomainModel\Payment\BankTransaction;
use App\Support\DateFormat;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="OrderPaymentDTO", type="object", properties={
 *      @OA\Property(property="amount", type="number", format="float"),
 *      @OA\Property(property="created_at", ref="#/components/schemas/DateTime"),
 *      @OA\Property(property="state", type="string"),
 *      @OA\Property(property="type", type="string"),
 *      @OA\Property(property="transaction_uuid", ref="#/components/schemas/UUID"),
 *      @OA\Property(property="debtor_name", type="string")
 * })
 * @deprecated use BankTransaction
 * @see BankTransaction
 */
class OrderPaymentDTO
{
    public const PAYMENT_TYPE_INVOICE_PAYBACK = BankTransaction::TYPE_INVOICE_PAYBACK;

    public const PAYMENT_STATE_NEW = BankTransaction::STATE_NEW;

    public const PAYMENT_STATE_COMPLETE = BankTransaction::STATE_COMPLETE;

    private $createdAt;

    private $amount;

    private $type;

    private $state;

    private $transactionUuid;

    private $debtorName;

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): OrderPaymentDTO
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): OrderPaymentDTO
    {
        $this->amount = $amount;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): OrderPaymentDTO
    {
        $this->type = $type;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): OrderPaymentDTO
    {
        $this->state = $state;

        return $this;
    }

    public function getTransactionUuid(): ?string
    {
        return $this->transactionUuid;
    }

    public function setTransactionUuid(?string $transactionUuid): OrderPaymentDTO
    {
        $this->transactionUuid = $transactionUuid;

        return $this;
    }

    public function getDebtorName(): ?string
    {
        return $this->debtorName;
    }

    public function setDebtorName(?string $debtorName): OrderPaymentDTO
    {
        $this->debtorName = $debtorName;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->getAmount(),
            'created_at' => $this->getCreatedAt()->format(DateFormat::FORMAT_YMD_HIS),
            'state' => $this->getState(),
            'type' => $this->getType(),
            'transaction_uuid' => $this->getTransactionUuid(),
            'debtor_name' => $this->getDebtorName(),
        ];
    }
}
