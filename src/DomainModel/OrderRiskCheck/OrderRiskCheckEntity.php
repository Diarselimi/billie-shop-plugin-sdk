<?php

namespace App\DomainModel\OrderRiskCheck;

use App\DomainModel\AbstractEntity;

class OrderRiskCheckEntity extends AbstractEntity
{
    private $orderId;

    private $riskCheckDefinition;

    private $isPassed;

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): OrderRiskCheckEntity
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getRiskCheckDefinition(): RiskCheckDefinitionEntity
    {
        return $this->riskCheckDefinition;
    }

    public function setRiskCheckDefinition(RiskCheckDefinitionEntity $riskCheckDefinition): OrderRiskCheckEntity
    {
        $this->riskCheckDefinition = $riskCheckDefinition;

        return $this;
    }

    public function isPassed(): bool
    {
        return $this->isPassed;
    }

    public function setIsPassed(bool $isPassed): OrderRiskCheckEntity
    {
        $this->isPassed = $isPassed;

        return $this;
    }
}
