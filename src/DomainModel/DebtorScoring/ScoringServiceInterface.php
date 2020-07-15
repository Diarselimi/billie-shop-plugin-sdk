<?php

namespace App\DomainModel\DebtorScoring;

interface ScoringServiceInterface
{
    public function scoreDebtor(DebtorScoringRequestDTO $requestDTO): DebtorScoringResponseDTO;
}
