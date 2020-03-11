<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use Ozean12\Money\Money;

class AmountCheck implements CheckInterface
{
    public const NAME = 'amount';

    private const MAX_AMOUNT = 50000;

    public function check(OrderContainer $orderContainer): CheckResult
    {
        $amount = $orderContainer->getOrderFinancialDetails()->getAmountGross();
        $minAmount = $orderContainer->getMerchantSettings()->getMinOrderAmount();

        $result = $amount->greaterThanOrEqual($minAmount) && $amount->lessThanOrEqual(self::MAX_AMOUNT);

        return new CheckResult($result, self::NAME);
    }
}
