<?php

namespace App\DomainModel\RiskCheck\Checker;

use App\DomainModel\Order\OrderContainer;

interface CheckInterface
{
    public function check(OrderContainer $order): CheckResult;
}
