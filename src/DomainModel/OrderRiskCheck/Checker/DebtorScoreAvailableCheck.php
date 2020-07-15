<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorScoring\DebtorScoringResponseDTO;
use App\DomainModel\OrderRiskCheck\CheckResult;

class DebtorScoreAvailableCheck extends AbstractDebtorScoreCheck
{
    public const NAME = 'debtor_score_available';

    protected function getName(): string
    {
        return self::NAME;
    }

    protected function createCheckResult(DebtorScoringResponseDTO $scoringResponse): CheckResult
    {
        return new CheckResult($scoringResponse->hasFailed() === false, $this->getName());
    }
}
