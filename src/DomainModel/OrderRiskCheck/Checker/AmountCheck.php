<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

class AmountCheck implements CheckInterface
{
    public const NAME = 'amount';

    private const MAX_AMOUNT = 50000;

    public function check(OrderContainer $order): CheckResult
    {
        $amount = $order->getOrder()->getAmountGross();
        $result = $amount <= self::MAX_AMOUNT;

        return new CheckResult($result, self::NAME);
    }
}
