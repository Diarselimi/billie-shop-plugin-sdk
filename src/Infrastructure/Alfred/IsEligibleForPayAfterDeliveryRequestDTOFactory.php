<?php

namespace App\Infrastructure\Alfred;

use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationEntity;
use App\DomainModel\ScoreThresholdsConfiguration\ScoreThresholdsConfigurationService;

class IsEligibleForPayAfterDeliveryRequestDTOFactory
{
    private $scoreThresholdsConfigurationService;

    public function __construct(ScoreThresholdsConfigurationService $scoreThresholdsConfigurationService)
    {
        $this->scoreThresholdsConfigurationService = $scoreThresholdsConfigurationService;
    }

    public function create(
        int $debtorId,
        bool $isSoleTrader,
        bool $debtorHasAtLeastOneFullyPaidOrder,
        ScoreThresholdsConfigurationEntity $merchantScoreThresholds,
        ?ScoreThresholdsConfigurationEntity $debtorScoreThresholds
    ): IsEligibleForPayAfterDeliveryRequestDTO {
        return (new IsEligibleForPayAfterDeliveryRequestDTO())
            ->setDebtorId($debtorId)
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
