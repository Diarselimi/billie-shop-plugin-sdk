<?php

declare(strict_types=1);

namespace App\DomainModel\DebtorScoring;

class DebtorScoringResponseDTO
{
    private $decisionUuid;

    private $isEligible;

    private $hasFailed;

    public function isEligible(): bool
    {
        return $this->isEligible;
    }

    public function setIsEligible(bool $isEligible): DebtorScoringResponseDTO
    {
        $this->isEligible = $isEligible;

        return $this;
    }

    public function hasFailed(): bool
    {
        return $this->hasFailed;
    }

    public function setHasFailed(bool $hasFailed): DebtorScoringResponseDTO
    {
        $this->hasFailed = $hasFailed;

        return $this;
    }

    public function getDecisionUuid(): ?string
    {
        return $this->decisionUuid;
    }

    public function setDecisionUuid(?string $decisionUuid): DebtorScoringResponseDTO
    {
        $this->decisionUuid = $decisionUuid;

        return $this;
    }
}
