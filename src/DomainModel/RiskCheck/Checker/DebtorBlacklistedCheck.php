<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

class DebtorBlacklistedCheck implements CheckInterface
{
    public const NAME = 'debtor_blacklisted';

    public function check(OrderContainer $order): CheckResult
    {
        return new CheckResult(!$order->getMerchantDebtor()->getDebtorCompany()->isBlacklisted(), self::NAME, []);
    }
}
