<?php

namespace App\DomainModel\ScoreThresholdsConfiguration;

use App\DomainModel\AbstractEntity;

class ScoreThresholdsConfigurationEntity extends AbstractEntity
{
    private $crefoLowScoreThreshold;

    private $crefoHighScoreThreshold;

    private $schufaLowScoreThreshold;

    private $schufaAverageScoreThreshold;

    private $schufaHighScoreThreshold;

    private $schufaSoleTraderScoreThreshold;

    public function getCrefoLowScoreThreshold(): int
    {
        return $this->crefoLowScoreThreshold;
    }

    public function setCrefoLowScoreThreshold(int $crefoLowScoreThreshold): ScoreThresholdsConfigurationEntity
    {
        $this->crefoLowScoreThreshold = $crefoLowScoreThreshold;

        return $this;
    }

    public function getCrefoHighScoreThreshold(): int
    {
        return $this->crefoHighScoreThreshold;
    }

    public function setCrefoHighScoreThreshold(int $crefoHighScoreThreshold): ScoreThresholdsConfigurationEntity
    {
        $this->crefoHighScoreThreshold = $crefoHighScoreThreshold;

        return $this;
    }

    public function getSchufaLowScoreThreshold(): int
    {
        return $this->schufaLowScoreThreshold;
    }

    public function setSchufaLowScoreThreshold(int $schufaLowScoreThreshold): ScoreThresholdsConfigurationEntity
    {
        $this->schufaLowScoreThreshold = $schufaLowScoreThreshold;

        return $this;
    }

    public function getSchufaAverageScoreThreshold(): int
    {
        return $this->schufaAverageScoreThreshold;
    }

    public function setSchufaAverageScoreThreshold(int $schufaAverageScoreThreshold): ScoreThresholdsConfigurationEntity
    {
        $this->schufaAverageScoreThreshold = $schufaAverageScoreThreshold;

        return $this;
    }

    public function getSchufaHighScoreThreshold(): int
    {
        return $this->schufaHighScoreThreshold;
    }

    public function setSchufaHighScoreThreshold(int $schufaHighScoreThreshold): ScoreThresholdsConfigurationEntity
    {
        $this->schufaHighScoreThreshold = $schufaHighScoreThreshold;

        return $this;
    }

    public function getSchufaSoleTraderScoreThreshold(): ? int
    {
        return $this->schufaSoleTraderScoreThreshold;
    }

    public function setSchufaSoleTraderScoreThreshold(?int $schufaSoleTraderScoreThreshold): ScoreThresholdsConfigurationEntity
    {
        $this->schufaSoleTraderScoreThreshold = $schufaSoleTraderScoreThreshold;

        return $this;
    }
}
