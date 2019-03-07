<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

class AvailableFinancingLimitCheck implements CheckInterface
{
    public const NAME = 'available_financing_limit';

    public function check(OrderContainer $order): CheckResult
    {
        return new CheckResult(
            $order->getMerchant()->getAvailableFinancingLimit() > $order->getOrder()->getAmountGross(),
            self::NAME
        );
    }
}
