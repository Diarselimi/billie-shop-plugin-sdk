<?php

namespace App\DomainModel\Borscht;

class OrderPaymentDetailsDTO
{
    private $payoutAmount;
    private $feeAmount;
    private $feeRate;
    private $dueDate;

    public function getPayoutAmount(): float
    {
        return $this->payoutAmount;
    }

    public function setPayoutAmount(float $payoutAmount): OrderPaymentDetailsDTO
    {
        $this->payoutAmount = $payoutAmount;

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
}
