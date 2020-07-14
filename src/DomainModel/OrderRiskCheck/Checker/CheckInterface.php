<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\CheckResult;

interface CheckInterface
{
    public function check(OrderContainer $orderContainer): CheckResult;
}
