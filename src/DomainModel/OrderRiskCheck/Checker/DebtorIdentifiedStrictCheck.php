<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\CheckResult;

class DebtorIdentifiedStrictCheck implements CheckInterface
{
    public const NAME = 'debtor_identified_strict';

    public function check(OrderContainer $orderContainer): CheckResult
    {
        return new CheckResult(
            !is_null($orderContainer->getMerchantDebtor()) && $orderContainer->getIdentifiedDebtorCompany()->isStrictMatch(),
            self::NAME
        );
    }
}
