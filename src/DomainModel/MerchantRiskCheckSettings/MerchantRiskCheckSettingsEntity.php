<?php

namespace App\DomainModel\MerchantRiskCheckSettings;

use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionEntity;
use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantRiskCheckSettingsEntity extends AbstractTimestampableEntity
{
    private $merchantId;

    private $riskCheckDefinition;

    private $enabled;

    private $declineOnFailure;

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): MerchantRiskCheckSettingsEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getRiskCheckDefinition(): RiskCheckDefinitionEntity
    {
        return $this->riskCheckDefinition;
    }

    public function setRiskCheckDefinition(
        RiskCheckDefinitionEntity $riskCheckDefinition
    ): MerchantRiskCheckSettingsEntity {
        $this->riskCheckDefinition = $riskCheckDefinition;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): MerchantRiskCheckSettingsEntity
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isDeclineOnFailure(): bool
    {
        return $this->declineOnFailure;
    }

    public function setDeclineOnFailure(bool $declineOnFailure): MerchantRiskCheckSettingsEntity
    {
        $this->declineOnFailure = $declineOnFailure;

        return $this;
    }
}
