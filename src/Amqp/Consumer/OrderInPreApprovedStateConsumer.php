<?php

namespace App\Amqp\Consumer;

use App\DomainModel\Order\OrderStateManager;

class OrderInPreApprovedStateConsumer extends AbstractOrderDeclineByStateConsumer
{
    protected function getTargetedStates(): array
    {
        return [OrderStateManager::STATE_PRE_APPROVED];
    }
}
