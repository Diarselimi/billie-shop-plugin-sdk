<?php

declare(strict_types=1);

namespace App\DomainModel\Order\DomainEvent;

use Ozean12\AmqpPackBundle\DomainEvent;

class OrderDebtorIdentificationV2DomainEvent extends DomainEvent
{
    private ?int $orderId;

    private ?int $v1CompanyId;

    public function __construct(?int $orderId, ?int $v1CompanyId)
    {
        $this->orderId = $orderId;
        $this->v1CompanyId = $v1CompanyId;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function getV1CompanyId(): ?int
    {
        return $this->v1CompanyId;
    }
}
