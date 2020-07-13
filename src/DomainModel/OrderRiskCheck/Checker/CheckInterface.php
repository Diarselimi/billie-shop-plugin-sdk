<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;

interface CheckInterface
{
    public function check(OrderContainer $orderContainer): CheckResult;
}
