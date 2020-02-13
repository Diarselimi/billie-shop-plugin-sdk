<?php

namespace App\Amqp\Consumer;

use App\DomainModel\Order\OrderStateManager;

class OrderInWaitingStateConsumer extends AbstractOrderDeclineByStateConsumer
{
    protected function getTargetedStates(): array
    {
        return [OrderStateManager::STATE_WAITING];
    }
}
