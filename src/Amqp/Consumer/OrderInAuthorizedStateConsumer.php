<?php

namespace App\Amqp\Consumer;

use App\DomainModel\Order\OrderStateManager;

class OrderInAuthorizedStateConsumer extends AbstractOrderDeclineByStateConsumer
{
    protected function getTargetedStates(): array
    {
        return [OrderStateManager::STATE_AUTHORIZED];
    }
}
