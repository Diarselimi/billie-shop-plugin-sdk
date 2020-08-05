<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck\DummyChecker;

use App\DomainModel\OrderRiskCheck\Checker\DebtorScoreAvailableCheck;

class DebtorScoreAvailableDummyCheck extends AbstractDummyCheck
{
    protected function getName(): string
    {
        return DebtorScoreAvailableCheck::NAME;
    }
}
