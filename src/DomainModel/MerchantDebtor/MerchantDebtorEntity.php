<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\DebtorCompany\DebtorCompany;
use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantDebtorEntity extends AbstractTimestampableEntity
{
    private $merchantId;

    private $debtorId;

    private $paymentDebtorId;

    private $debtorCompany;

    private $scoreThresholdsConfigurationId;

    private $isWhitelisted;

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

    public function getDebtorCompany(): DebtorCompany
    {
        return $this->debtorCompany;
    }

    public function setDebtorCompany(DebtorCompany $debtorCompany): MerchantDebtorEntity
    {
        $this->debtorCompany = $debtorCompany;

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
