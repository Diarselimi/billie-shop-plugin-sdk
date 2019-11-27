<?php

namespace App\DomainModel\Order;

use Billie\PdoBundle\DomainModel\StateTransitionEntity\AbstractStateTransitionEntity;

class OrderStateTransitionEntity extends AbstractStateTransitionEntity
{
    private $orderId;

    public function getReferenceId(): int
    {
        return $this->orderId;
    }

    public function setReferenceId(int $referenceId): OrderStateTransitionEntity
    {
        $this->orderId = $referenceId;

        return $this;
    }
}
