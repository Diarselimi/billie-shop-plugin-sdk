<?php

namespace App\DomainModel\DebtorScoring;

use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationService;

class DebtorScoringRequestDTOFactory
{
    private $scoreThresholdsConfigurationService;

    public function __construct(ScoreThresholdsConfigurationService $scoreThresholdsConfigurationService)
    {
        $this->scoreThresholdsConfigurationService = $scoreThresholdsConfigurationService;
    }

    public function create(
        string $debtorUuid,
        bool $isSoleTrader,
        bool $debtorHasAtLeastOneFullyPaidOrder,
        ScoreThresholdsConfigurationEntity $merchantScoreThresholds,
        ?ScoreThresholdsConfigurationEntity $debtorScoreThresholds
    ): DebtorScoringRequestDTO {
        return (new DebtorScoringRequestDTO())
            ->setDebtorUuid($debtorUuid)
            ->setIsSoleTrader($isSoleTrader)
            ->setHasPaidInvoice($debtorHasAtLeastOneFullyPaidOrder)
            ->setCrefoLowScoreThreshold($this->scoreThresholdsConfigurationService->getCrefoLowScoreThreshold($merchantScoreThresholds, $debtorScoreThresholds))
            ->setCrefoHighScoreThreshold($this->scoreThresholdsConfigurationService->getCrefoHighScoreThreshold($merchantScoreThresholds, $debtorScoreThresholds))
            ->setSchufaLowScoreThreshold($this->scoreThresholdsConfigurationService->getSchufaLowScoreThreshold($merchantScoreThresholds, $debtorScoreThresholds))
            ->setSchufaAverageScoreThreshold($this->scoreThresholdsConfigurationService->getSchufaAverageScoreThreshold($merchantScoreThresholds, $debtorScoreThresholds))
            ->setSchufaHighScoreThreshold($this->scoreThresholdsConfigurationService->getSchufaHighScoreThreshold($merchantScoreThresholds, $debtorScoreThresholds))
            ->setSchufaSoleTraderScoreThreshold($this->scoreThresholdsConfigurationService->getSchufaSoleTraderScoreThreshold($merchantScoreThresholds, $debtorScoreThresholds))
        ;
    }
}
