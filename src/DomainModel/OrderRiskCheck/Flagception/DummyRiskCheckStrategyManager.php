<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck\Flagception;

use App\DomainModel\Order\OrderContainer\OrderContainer;

final class DummyRiskCheckStrategyManager
{
    private $dummyStrategies;

    public function __construct(DummyRiskCheckStrategyInterface ...$dummyStrategies)
    {
        $this->dummyStrategies = $dummyStrategies;
    }

    public function isActive(string $riskCheckName, OrderContainer $orderContainer): bool
    {
        foreach ($this->dummyStrategies as $dummyStrategy) {
            if ($dummyStrategy->supports($riskCheckName)) {
                return $dummyStrategy->isActive($orderContainer);
            }
        }

        return false;
    }
}
