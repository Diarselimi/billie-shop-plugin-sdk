<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

interface CheckInterface
{
    public function check(OrderContainer $order): CheckResult;
}
