<?php

namespace App\DomainModel\Payment;

class OrderAmountChangeDTO
{
    public const TYPE_CANCELLATION = 'cancellation';

    public const TYPE_PAYMENT = 'payment';

    private $id;

    private $type;

    private $amountChange;

    private $outstandingAmount;

    private $paidAmount;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): OrderAmountChangeDTO
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): OrderAmountChangeDTO
    {
        $this->type = $type;

        return $this;
    }

    public function getAmountChange(): float
    {
        return $this->amountChange;
    }

    public function setAmountChange(float $amountChange): OrderAmountChangeDTO
    {
        $this->amountChange = $amountChange;

        return $this;
    }

    public function getOutstandingAmount(): float
    {
        return $this->outstandingAmount;
    }

    public function setOutstandingAmount(float $outstandingAmount): OrderAmountChangeDTO
    {
        $this->outstandingAmount = $outstandingAmount;

        return $this;
    }

    public function getPaidAmount(): float
    {
        return $this->paidAmount;
    }

    public function setPaidAmount(float $paidAmount): OrderAmountChangeDTO
    {
        $this->paidAmount = $paidAmount;

        return $this;
    }

    public function isPayment(): bool
    {
        return $this->getType() === self::TYPE_PAYMENT;
    }
}
