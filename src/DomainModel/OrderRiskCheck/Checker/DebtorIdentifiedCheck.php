<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

class DebtorIdentifiedCheck implements CheckInterface
{
    public const NAME = 'debtor_identified';

    public function check(OrderContainer $order): CheckResult
    {
        return new CheckResult(!is_null($order->getMerchantDebtor()), self::NAME);
    }
}
