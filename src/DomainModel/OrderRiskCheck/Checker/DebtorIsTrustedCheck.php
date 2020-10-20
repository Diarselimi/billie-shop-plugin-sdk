<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\CheckResult;

class DebtorIsTrustedCheck implements CheckInterface
{
    public const NAME = "debtor_is_trusted";

    public function check(OrderContainer $orderContainer): CheckResult
    {
        if ($orderContainer->getIdentifiedDebtorCompany()->isTrustedSource()) {
            return new CheckResult(true, self::NAME);
        }

        return new CheckResult($orderContainer->getDebtorSettings()->isWhitelisted(), self::NAME);
    }
}
