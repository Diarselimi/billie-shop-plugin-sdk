<?php

namespace App\DomainModel\MerchantDebtor;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantDebtorEntity extends AbstractTimestampableEntity
{
    private $uuid;

    private $merchantId;

    private $debtorId;

    private $paymentDebtorId;

    private $scoreThresholdsConfigurationId;

    private $isWhitelisted;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): MerchantDebtorEntity
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function setMerchantId(string $merchantId): MerchantDebtorEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getDebtorId(): string
    {
        return $this->debtorId;
    }

    public function setDebtorId(string $debtorId): MerchantDebtorEntity
    {
        $this->debtorId = $debtorId;

        return $this;
    }

    public function getPaymentDebtorId(): ?string
    {
        return $this->paymentDebtorId;
    }

    public function setPaymentDebtorId(?string $paymentDebtorId): MerchantDebtorEntity
    {
        $this->paymentDebtorId = $paymentDebtorId;

        return $this;
    }

    public function getScoreThresholdsConfigurationId(): ?int
    {
        return $this->scoreThresholdsConfigurationId;
    }

    public function setScoreThresholdsConfigurationId(?int $scoreThresholdsConfigurationId): MerchantDebtorEntity
    {
        $this->scoreThresholdsConfigurationId = $scoreThresholdsConfigurationId;

        return $this;
    }

    public function isWhitelisted(): bool
    {
        return $this->isWhitelisted;
    }

    public function setIsWhitelisted(bool $isWhitelisted): MerchantDebtorEntity
    {
        $this->isWhitelisted = $isWhitelisted;

        return $this;
    }
}
