<?php

namespace App\DomainModel\MerchantSettings;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantSettingsEntity extends AbstractTimestampableEntity
{
    private $merchantId;

    private $debtorFinancingLimit;

    private $minOrderAmount;

    private $scoreThresholdsConfigurationId;

    private $useExperimentalDebtorIdentification;

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getDebtorFinancingLimit(): float
    {
        return $this->debtorFinancingLimit;
    }

    public function getMinOrderAmount(): float
    {
        return $this->minOrderAmount;
    }

    public function setMerchantId(int $merchantId): MerchantSettingsEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function setDebtorFinancingLimit(float $debtorFinancingLimit): MerchantSettingsEntity
    {
        $this->debtorFinancingLimit = $debtorFinancingLimit;

        return $this;
    }

    public function setMinOrderAmount(float $minOrderAmount): MerchantSettingsEntity
    {
        $this->minOrderAmount = $minOrderAmount;

        return $this;
    }

    public function getScoreThresholdsConfigurationId(): int
    {
        return $this->scoreThresholdsConfigurationId;
    }

    public function setScoreThresholdsConfigurationId(int $scoreThresholdsConfigurationId): MerchantSettingsEntity
    {
        $this->scoreThresholdsConfigurationId = $scoreThresholdsConfigurationId;

        return $this;
    }

    public function useExperimentalDebtorIdentification(): bool
    {
        return $this->useExperimentalDebtorIdentification;
    }

    public function setUseExperimentalDebtorIdentification(bool $useExperimentalDebtorIdentification): MerchantSettingsEntity
    {
        $this->useExperimentalDebtorIdentification = $useExperimentalDebtorIdentification;

        return $this;
    }
}
