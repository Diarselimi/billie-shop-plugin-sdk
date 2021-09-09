<?php

declare(strict_types=1);

namespace App\DomainModel\Order\DomainEvent;

use Ozean12\AmqpPackBundle\DomainEvent;

abstract class AbstractOrderStateDomainEvent extends DomainEvent
{
    private string $orderId;

    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }
}
