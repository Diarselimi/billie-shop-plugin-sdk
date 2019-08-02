<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;

class AvailableFinancingLimitCheck implements CheckInterface
{
    public const NAME = 'available_financing_limit';

    public function check(OrderContainer $orderContainer): CheckResult
    {
        return new CheckResult(
            $orderContainer->getMerchant()->getFinancingPower() > $orderContainer->getOrderFinancialDetails()->getAmountGross(),
            self::NAME
        );
    }
}
