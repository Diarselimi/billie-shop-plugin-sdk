<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

class DebtorIdentifiedStrictCheck implements CheckInterface
{
    public const NAME = 'debtor_identified_strict';

    public function check(OrderContainer $order): CheckResult
    {
        return new CheckResult(
            !is_null($order->getMerchantDebtor()) && $order->getMerchantDebtor()->getDebtorCompany()->isStrictMatch(),
            self::NAME
        );
    }
}
