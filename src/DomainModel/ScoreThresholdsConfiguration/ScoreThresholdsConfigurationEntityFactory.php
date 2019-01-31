<?php

namespace App\DomainModel\ScoreThresholdsConfiguration;

class ScoreThresholdsConfigurationEntityFactory
{
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
}
