<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;

class DebtorIdentifiedCheck implements CheckInterface
{
    public const NAME = 'debtor_identified';

    public function check(OrderContainer $orderContainer): CheckResult
    {
        return new CheckResult(($orderContainer->getOrder()->getMerchantDebtorId() !== null), self::NAME);
    }
}
