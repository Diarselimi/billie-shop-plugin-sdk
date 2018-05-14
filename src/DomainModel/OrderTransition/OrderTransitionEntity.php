<?php

namespace App\DomainModel\OrderTransition;

class OrderTransitionEntity
{
    private $id;
    private $orderId;
    private $transition;
    private $transitedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): OrderTransitionEntity
    {
        $this->id = $id;

        return $this;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): OrderTransitionEntity
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getTransition(): string
    {
        return $this->transition;
    }

    public function setTransition(string $transition): OrderTransitionEntity
    {
        $this->transition = $transition;

        return $this;
    }

    public function getTransitedAt(): \DateTime
    {
        return $this->transitedAt;
    }

    public function setTransitedAt(\DateTime $transitedAt): OrderTransitionEntity
    {
        $this->transitedAt = $transitedAt;

        return $this;
    }
}
