<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

class DebtorIsTrusted implements CheckInterface
{
    public const NAME = "debtor_is_trusted";

    public function check(OrderContainer $orderContainer): CheckResult
    {
        if ($orderContainer->getMerchantDebtor()->getDebtorCompany()->isTrustedSource()) {
            return new CheckResult(true, self::NAME);
        }

        return new CheckResult($orderContainer->getMerchantDebtor()->isWhitelisted(), self::NAME);
    }
}
