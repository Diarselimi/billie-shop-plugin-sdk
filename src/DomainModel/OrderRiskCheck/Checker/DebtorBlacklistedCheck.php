<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\CheckResult;

class DebtorBlacklistedCheck implements CheckInterface
{
    public const NAME = 'debtor_blacklisted';

    public function check(OrderContainer $orderContainer): CheckResult
    {
        return new CheckResult(!$orderContainer->getDebtorCompany()->isBlacklisted(), self::NAME);
    }
}
