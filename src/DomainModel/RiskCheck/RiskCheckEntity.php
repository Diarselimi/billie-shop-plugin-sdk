<?php

namespace App\DomainModel\RiskCheck;

use App\DomainModel\AbstractEntity;

class RiskCheckEntity extends AbstractEntity
{
    private $checkId;
    private $orderId;
    private $name;
    private $isPassed;

    public function getCheckId(): int
    {
        return $this->checkId;
    }

    public function setCheckId(int $checkId): RiskCheckEntity
    {
        $this->checkId = $checkId;

        return $this;
    }

    public function getOrderId():int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): RiskCheckEntity
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): RiskCheckEntity
    {
        $this->name = $name;

        return $this;
    }

    public function isPassed(): bool
    {
        return $this->isPassed;
    }

    public function setIsPassed(bool $isPassed): RiskCheckEntity
    {
        $this->isPassed = $isPassed;

        return $this;
    }
}
