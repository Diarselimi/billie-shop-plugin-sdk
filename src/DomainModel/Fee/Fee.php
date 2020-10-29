<?php

namespace App\DomainModel\Fee;

use App\DomainModel\ArrayableInterface;
use Ozean12\Money\Money;
use Ozean12\Money\Percent;

class Fee implements ArrayableInterface
{
    private Percent $feeRate;

    private Money $grossFeeAmount;

    private Money $netFeeAmount;

    private Money $taxFeeAmount;

    public function __construct(Percent $feeRate, Money $grossFeeAmount, Money $netFeeAmount, Money $taxFeeAmount)
    {
        $this->feeRate = $feeRate;
        $this->grossFeeAmount = $grossFeeAmount;
        $this->netFeeAmount = $netFeeAmount;
        $this->taxFeeAmount = $taxFeeAmount;
    }

    public function getFeeRate(): Percent
    {
        return $this->feeRate;
    }

    public function getGrossFeeAmount(): Money
    {
        return $this->grossFeeAmount;
    }

    public function getNetFeeAmount(): Money
    {
        return $this->netFeeAmount;
    }

    public function getTaxFeeAmount(): Money
    {
        return $this->taxFeeAmount;
    }

    public function toArray(): array
    {
        return [
            'fee_rate' => $this->feeRate->toFloat(),
            'net_fee_amount' => $this->netFeeAmount->toFloat(),
            'tax_fee_amount' => $this->taxFeeAmount->toFloat(),
            'gross_fee_amount' => $this->grossFeeAmount->toFloat(),
        ];
    }
}
