<?php

namespace App\DomainModel\Order;

class OrderStateManager
{
    const STATE_NEW = 'new';
    const STATE_SHIPPED = 'shipped';

    public function wasShipped(OrderEntity $order): bool
    {
        return in_array($order->getState(), [self::STATE_SHIPPED]);
    }
}
