<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck\Flagception\DummyStrategy;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\Checker\DebtorScoreAvailableCheck;
use App\DomainModel\OrderRiskCheck\Flagception\DummyRiskCheckStrategyInterface;

final class DebtorScoreAvailableDummyRiskCheckStrategy implements DummyRiskCheckStrategyInterface
{
    public function supports(string $riskCheckName): bool
    {
        return $riskCheckName === DebtorScoreAvailableCheck::NAME;
    }

    public function isActive(OrderContainer $orderContainer): bool
    {
        return $orderContainer->getDebtorCompany()->getName() === 'Debtor Score Available GmbH';
    }
}
