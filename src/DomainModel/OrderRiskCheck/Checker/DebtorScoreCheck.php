<?php

declare(strict_types=1);

namespace App\DomainModel\OrderRiskCheck\Checker;

use App\DomainModel\DebtorScoring\DebtorScoringResponseDTO;
use App\DomainModel\OrderRiskCheck\CheckResult;

class DebtorScoreCheck extends AbstractDebtorScoreCheck
{
    public const NAME = 'company_b2b_score';

    protected function getName(): string
    {
        return self::NAME;
    }

    protected function createCheckResult(DebtorScoringResponseDTO $scoringResponse): CheckResult
    {
        return new CheckResult($scoringResponse->isEligible(), $this->getName());
    }
}
