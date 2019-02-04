<?php

namespace App\DomainModel\ScoreThresholdsConfiguration;

class ScoreThresholdsConfigurationService
{
    public function getCrefoLowScoreThreshold(
        ScoreThresholdsConfigurationEntity $merchantScoreThresholds = null,
        ScoreThresholdsConfigurationEntity $debtorScoreThresholds = null
    ): int {
        if (!is_null($debtorScoreThresholds)) {
            return $debtorScoreThresholds->getCrefoLowScoreThreshold();
        }

        return $merchantScoreThresholds->getCrefoLowScoreThreshold();
    }

    public function getCrefoHighScoreThreshold(
        ScoreThresholdsConfigurationEntity $merchantScoreThresholds = null,
        ScoreThresholdsConfigurationEntity $debtorScoreThresholds = null
    ): int {
        if (!is_null($debtorScoreThresholds)) {
            return $debtorScoreThresholds->getCrefoHighScoreThreshold();
        }

        return $merchantScoreThresholds->getCrefoHighScoreThreshold();
    }

    public function getSchufaLowScoreThreshold(
        ScoreThresholdsConfigurationEntity $merchantScoreThresholds = null,
        ScoreThresholdsConfigurationEntity $debtorScoreThresholds = null
    ): int {
        if (!is_null($debtorScoreThresholds)) {
            return $debtorScoreThresholds->getSchufaLowScoreThreshold();
        }

        return $merchantScoreThresholds->getSchufaLowScoreThreshold();
    }

    public function getSchufaAverageScoreThreshold(
        ScoreThresholdsConfigurationEntity $merchantScoreThresholds = null,
        ScoreThresholdsConfigurationEntity $debtorScoreThresholds = null
    ): int {
        if (!is_null($debtorScoreThresholds)) {
            return $debtorScoreThresholds->getSchufaAverageScoreThreshold();
        }

        return $merchantScoreThresholds->getSchufaAverageScoreThreshold();
    }

    public function getSchufaHighScoreThreshold(
        ScoreThresholdsConfigurationEntity $merchantScoreThresholds = null,
        ScoreThresholdsConfigurationEntity $debtorScoreThresholds = null
    ): int {
        if (!is_null($debtorScoreThresholds)) {
            return $debtorScoreThresholds->getSchufaHighScoreThreshold();
        }

        return $merchantScoreThresholds->getSchufaHighScoreThreshold();
    }

    public function getSchufaSoleTraderScoreThreshold(
        ScoreThresholdsConfigurationEntity $merchantScoreThresholds = null,
        ScoreThresholdsConfigurationEntity $debtorScoreThresholds = null
    ): int {
        if (!is_null($debtorScoreThresholds)) {
            return $debtorScoreThresholds->getSchufaSoleTraderScoreThreshold();
        }

        return $merchantScoreThresholds->getSchufaSoleTraderScoreThreshold();
    }
}
