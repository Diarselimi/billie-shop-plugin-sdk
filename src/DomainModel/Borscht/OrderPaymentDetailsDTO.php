<?php

namespace App\DomainModel\Borscht;

class OrderPaymentDetailsDTO
{
    private const STATE_LATE = 'late';

    private $id;
    private $state;
    private $payoutAmount;
    private $outstandingAmount;
    private $feeAmount;
    private $feeRate;
    private $dueDate;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): OrderPaymentDetailsDTO
    {
        $this->id = $id;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): OrderPaymentDetailsDTO
    {
        $this->state = $state;

        return $this;
    }

    public function getPayoutAmount(): float
    {
        return $this->payoutAmount;
    }

    public function setPayoutAmount(float $payoutAmount): OrderPaymentDetailsDTO
    {
        $this->payoutAmount = $payoutAmount;

        return $this;
    }

    public function getOutstandingAmount(): float
    {
        return $this->outstandingAmount;
    }

    public function setOutstandingAmount(float $outstandingAmount): OrderPaymentDetailsDTO
    {
        $this->outstandingAmount = $outstandingAmount;

        return $this;
    }

    public function getFeeAmount(): float
    {
        return $this->feeAmount;
    }

    public function setFeeAmount(float $feeAmount): OrderPaymentDetailsDTO
    {
        $this->feeAmount = $feeAmount;

        return $this;
    }

    public function getFeeRate(): float
    {
        return $this->feeRate;
    }

    public function setFeeRate(float $feeRate): OrderPaymentDetailsDTO
    {
        $this->feeRate = $feeRate;

        return $this;
    }

    public function getDueDate(): \DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): OrderPaymentDetailsDTO
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function isLate(): bool
    {
        return $this->getState() === self::STATE_LATE;
    }
}
