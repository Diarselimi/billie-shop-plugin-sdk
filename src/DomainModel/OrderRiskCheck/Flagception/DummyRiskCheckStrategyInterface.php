<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck\Flagception;

use App\DomainModel\Order\OrderContainer\OrderContainer;

interface DummyRiskCheckStrategyInterface
{
    public function supports(string $riskCheckName): bool;

    public function isActive(OrderContainer $orderContainer): bool;
}
