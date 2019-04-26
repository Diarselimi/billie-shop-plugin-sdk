<?php

namespace App\DomainModel\MerchantSettings;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantSettingsEntity extends AbstractTimestampableEntity
{
    public const INVOICE_HANDLING_STRATEGY_FTP = 'ftp';

    public const INVOICE_HANDLING_STRATEGY_HTTP = 'http';

    public const INVOICE_HANDLING_STRATEGY_NONE = 'none';

    public const DEFAULT_DEBTOR_FORGIVENESS_THRESHOLD = 1.0;

    private $merchantId;

    private $debtorFinancingLimit;

    private $minOrderAmount;

    private $invoiceHandlingStrategy;

    private $scoreThresholdsConfigurationId;

    private $useExperimentalDebtorIdentification;

    private $debtorForgivenessThreshold;

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

    public function getInvoiceHandlingStrategy(): string
    {
        return $this->invoiceHandlingStrategy;
    }

    public function setInvoiceHandlingStrategy(string $invoiceHandlingStrategy): MerchantSettingsEntity
    {
        $this->invoiceHandlingStrategy = $invoiceHandlingStrategy;

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

    public function getDebtorForgivenessThreshold(): float
    {
        return $this->debtorForgivenessThreshold;
    }

    public function setDebtorForgivenessThreshold(float $debtorForgivenessThreshold): MerchantSettingsEntity
    {
        $this->debtorForgivenessThreshold = $debtorForgivenessThreshold;

        return $this;
    }
}
