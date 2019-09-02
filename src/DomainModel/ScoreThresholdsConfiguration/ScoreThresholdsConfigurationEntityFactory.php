<?php

namespace App\DomainModel\ScoreThresholdsConfiguration;

class ScoreThresholdsConfigurationEntityFactory
{
    public const DEFAULT_CREFO_LOW = 299;

    public const DEFAULT_CREFO_HIGH = 360;

    public const DEFAULT_SCHUFA_LOW = 270;

    public const DEFAULT_SCHUFA_AVERAGE = 293;

    public const DEFAULT_SCHUFA_HIGH = 360;

    public const DEFAULT_SCHUFA_SOLE_TRADER = 316;

    public function createFromDatabaseRow(array $row): ScoreThresholdsConfigurationEntity
    {
        return (new ScoreThresholdsConfigurationEntity())
            ->setId($row['id'])
            ->setCrefoLowScoreThreshold($row['crefo_low_score_threshold'])
            ->setCrefoHighScoreThreshold($row['crefo_high_score_threshold'])
            ->setSchufaLowScoreThreshold($row['schufa_low_score_threshold'])
            ->setSchufaAverageScoreThreshold($row['schufa_average_score_threshold'])
            ->setSchufaHighScoreThreshold($row['schufa_high_score_threshold'])
            ->setSchufaSoleTraderScoreThreshold($row['schufa_sole_trader_score_threshold'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }

    public function createDefault(): ScoreThresholdsConfigurationEntity
    {
        return (new ScoreThresholdsConfigurationEntity())
            ->setCrefoLowScoreThreshold(self::DEFAULT_CREFO_LOW)
            ->setCrefoHighScoreThreshold(self::DEFAULT_CREFO_HIGH)
            ->setSchufaLowScoreThreshold(self::DEFAULT_SCHUFA_LOW)
            ->setSchufaAverageScoreThreshold(self::DEFAULT_SCHUFA_AVERAGE)
            ->setSchufaHighScoreThreshold(self::DEFAULT_SCHUFA_HIGH)
            ->setSchufaSoleTraderScoreThreshold(self::DEFAULT_SCHUFA_SOLE_TRADER)
        ;
    }
}
