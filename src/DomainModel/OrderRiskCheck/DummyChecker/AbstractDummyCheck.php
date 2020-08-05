<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck\DummyChecker;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\Checker\CheckInterface;
use App\DomainModel\OrderRiskCheck\CheckResult;

abstract class AbstractDummyCheck implements DummyCheckInterface, CheckInterface
{
    abstract protected function getName(): string;

    public function check(OrderContainer $orderContainer): CheckResult
    {
        return new CheckResult(false, $this->getName());
    }
}
