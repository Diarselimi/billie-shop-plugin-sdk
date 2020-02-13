<?php

namespace App\Amqp\Consumer;

use App\DomainModel\Order\OrderStateManager;

class OrderInPreWaitingStateConsumer extends AbstractOrderDeclineByStateConsumer
{
    protected function getTargetedStates(): array
    {
        return [OrderStateManager::STATE_PRE_WAITING];
    }
}
