<?php

namespace App\Infrastructure\Alfred;

class IsEligibleForPayAfterDeliveryRequestDTO
{
    private $debtorId;

    private $isSoleTrader;

    private $hasPaidInvoice;

    private $crefoLowScoreThreshold;

    private $crefoHighScoreThreshold;

    private $schufaLowScoreThreshold;

    private $schufaAverageScoreThreshold;

    private $schufaHighScoreThreshold;

    private $schufaSoleTraderScoreThreshold;

    public function getDebtorId(): string
    {
        return $this->debtorId;
    }

    public function setDebtorId(string $debtorId): IsEligibleForPayAfterDeliveryRequestDTO
    {
        $this->debtorId = $debtorId;

        return $this;
    }

    public function isSoleTrader(): bool
    {
        return $this->isSoleTrader;
    }

    public function setIsSoleTrader(bool $isSoleTrader): IsEligibleForPayAfterDeliveryRequestDTO
    {
        $this->isSoleTrader = $isSoleTrader;

        return $this;
    }

    public function isHasPaidInvoice(): bool
    {
        return $this->hasPaidInvoice;
    }

    public function setHasPaidInvoice(bool $hasPaidInvoice): IsEligibleForPayAfterDeliveryRequestDTO
    {
        $this->hasPaidInvoice = $hasPaidInvoice;

        return $this;
    }

    public function getCrefoLowScoreThreshold(): int
    {
        return $this->crefoLowScoreThreshold;
    }

    public function setCrefoLowScoreThreshold(int $crefoLowScoreThreshold): IsEligibleForPayAfterDeliveryRequestDTO
    {
        $this->crefoLowScoreThreshold = $crefoLowScoreThreshold;

        return $this;
    }

    public function getCrefoHighScoreThreshold(): int
    {
        return $this->crefoHighScoreThreshold;
    }

    public function setCrefoHighScoreThreshold(int $crefoHighScoreThreshold): IsEligibleForPayAfterDeliveryRequestDTO
    {
        $this->crefoHighScoreThreshold = $crefoHighScoreThreshold;

        return $this;
    }

    public function getSchufaLowScoreThreshold(): int
    {
        return $this->schufaLowScoreThreshold;
    }

    public function setSchufaLowScoreThreshold(int $schufaLowScoreThreshold): IsEligibleForPayAfterDeliveryRequestDTO
    {
        $this->schufaLowScoreThreshold = $schufaLowScoreThreshold;

        return $this;
    }

    public function getSchufaAverageScoreThreshold(): int
    {
        return $this->schufaAverageScoreThreshold;
    }

    public function setSchufaAverageScoreThreshold(
        int $schufaAverageScoreThreshold
    ): IsEligibleForPayAfterDeliveryRequestDTO {
        $this->schufaAverageScoreThreshold = $schufaAverageScoreThreshold;

        return $this;
    }

    public function getSchufaHighScoreThreshold(): int
    {
        return $this->schufaHighScoreThreshold;
    }

    public function setSchufaHighScoreThreshold(int $schufaHighScoreThreshold): IsEligibleForPayAfterDeliveryRequestDTO
    {
        $this->schufaHighScoreThreshold = $schufaHighScoreThreshold;

        return $this;
    }

    public function getSchufaSoleTraderScoreThreshold(): ? int
    {
        return $this->schufaSoleTraderScoreThreshold;
    }

    public function setSchufaSoleTraderScoreThreshold(
        ?int $schufaSoleTraderScoreThreshold
    ): IsEligibleForPayAfterDeliveryRequestDTO {
        $this->schufaSoleTraderScoreThreshold = $schufaSoleTraderScoreThreshold;

        return $this;
    }
}
