<?php

namespace App\DomainModel\DebtorScoring;

class DebtorScoringRequestDTO
{
    private $debtorUuid;

    private $isSoleTrader;

    private $hasPaidInvoice;

    private $crefoLowScoreThreshold;

    private $crefoHighScoreThreshold;

    private $schufaLowScoreThreshold;

    private $schufaAverageScoreThreshold;

    private $schufaHighScoreThreshold;

    private $schufaSoleTraderScoreThreshold;

    public function getDebtorUuid(): string
    {
        return $this->debtorUuid;
    }

    public function setDebtorUuid(string $debtorUuid): DebtorScoringRequestDTO
    {
        $this->debtorUuid = $debtorUuid;

        return $this;
    }

    public function isSoleTrader(): bool
    {
        return $this->isSoleTrader;
    }

    public function setIsSoleTrader(bool $isSoleTrader): DebtorScoringRequestDTO
    {
        $this->isSoleTrader = $isSoleTrader;

        return $this;
    }

    public function isHasPaidInvoice(): bool
    {
        return $this->hasPaidInvoice;
    }

    public function setHasPaidInvoice(bool $hasPaidInvoice): DebtorScoringRequestDTO
    {
        $this->hasPaidInvoice = $hasPaidInvoice;

        return $this;
    }

    public function getCrefoLowScoreThreshold(): int
    {
        return $this->crefoLowScoreThreshold;
    }

    public function setCrefoLowScoreThreshold(int $crefoLowScoreThreshold): DebtorScoringRequestDTO
    {
        $this->crefoLowScoreThreshold = $crefoLowScoreThreshold;

        return $this;
    }

    public function getCrefoHighScoreThreshold(): int
    {
        return $this->crefoHighScoreThreshold;
    }

    public function setCrefoHighScoreThreshold(int $crefoHighScoreThreshold): DebtorScoringRequestDTO
    {
        $this->crefoHighScoreThreshold = $crefoHighScoreThreshold;

        return $this;
    }

    public function getSchufaLowScoreThreshold(): int
    {
        return $this->schufaLowScoreThreshold;
    }

    public function setSchufaLowScoreThreshold(int $schufaLowScoreThreshold): DebtorScoringRequestDTO
    {
        $this->schufaLowScoreThreshold = $schufaLowScoreThreshold;

        return $this;
    }

    public function getSchufaAverageScoreThreshold(): int
    {
        return $this->schufaAverageScoreThreshold;
    }

    public function setSchufaAverageScoreThreshold(int $schufaAverageScoreThreshold): DebtorScoringRequestDTO
    {
        $this->schufaAverageScoreThreshold = $schufaAverageScoreThreshold;

        return $this;
    }

    public function getSchufaHighScoreThreshold(): int
    {
        return $this->schufaHighScoreThreshold;
    }

    public function setSchufaHighScoreThreshold(int $schufaHighScoreThreshold): DebtorScoringRequestDTO
    {
        $this->schufaHighScoreThreshold = $schufaHighScoreThreshold;

        return $this;
    }

    public function getSchufaSoleTraderScoreThreshold(): ? int
    {
        return $this->schufaSoleTraderScoreThreshold;
    }

    public function setSchufaSoleTraderScoreThreshold(?int $schufaSoleTraderScoreThreshold): DebtorScoringRequestDTO
    {
        $this->schufaSoleTraderScoreThreshold = $schufaSoleTraderScoreThreshold;

        return $this;
    }
}
