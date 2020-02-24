<?php

namespace App\DomainModel\DebtorScoring;

interface ScoringServiceInterface
{
    public function isEligibleForPayAfterDelivery(DebtorScoringRequestDTO $requestDTO): bool;
}
