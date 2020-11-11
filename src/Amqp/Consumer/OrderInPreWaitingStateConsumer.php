<?php

namespace App\Amqp\Consumer;

use App\DomainModel\Order\OrderEntity;

class OrderInPreWaitingStateConsumer extends AbstractOrderDeclineByStateConsumer
{
    protected function getTargetedStates(): array
    {
        return [OrderEntity::STATE_PRE_WAITING];
    }
}
