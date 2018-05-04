<?php

namespace App\DomainModel\Order;

class OrderStateManager
{
    const STATE_NEW = 'new';
    const STATE_APPROVED = 'approved';
    const STATE_REJECTED = 'rejected';
    const STATE_SHIPPED = 'shipped';
    const STATE_CANCELLED = 'cancelled';

    const TRANSITION_APPROVE = 'approve';
    const TRANSITION_REJECT = 'reject';
    const TRANSITION_SHIP = 'ship';
    const TRANSITION_CANCEL = 'cancel';
    const TRANSITION_CANCEL_SHIPPED = 'cancel_shipped';

    public function wasShipped(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_SHIPPED;
    }
}
