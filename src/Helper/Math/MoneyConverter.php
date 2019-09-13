<?php

namespace App\Helper\Math;

class MoneyConverter
{
    public function toInt(float $number): int
    {
        return (int) bcmul($number, 100, 0);
    }

    public function toMoney(int $number): float
    {
        return bcdiv($number, 100, 2);
    }
}
