<?php

namespace App\Amqp\Consumer;

use App\DomainModel\Order\OrderEntity;

class OrderInWaitingStateConsumer extends AbstractOrderDeclineByStateConsumer
{
    protected function getTargetedStates(): array
    {
        return [OrderEntity::STATE_WAITING];
    }
}
