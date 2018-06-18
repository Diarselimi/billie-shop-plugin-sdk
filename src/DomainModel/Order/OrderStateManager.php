<?php

namespace App\DomainModel\Order;

class OrderStateManager
{
    const STATE_NEW = 'new';
    const STATE_CREATED = 'created';
    const STATE_DECLINED = 'declined';
    const STATE_SHIPPED = 'shipped';
    const STATE_PAID_OUT = 'paid_out';
    const STATE_LATE = 'late';
    const STATE_CANCELED = 'canceled';

    const TRANSITION_NEW = 'new';
    const TRANSITION_CREATE = 'create';
    const TRANSITION_DECLINE = 'decline';
    const TRANSITION_SHIP = 'ship';
    const TRANSITION_LATE = 'late';
    const TRANSITION_CANCEL = 'cancel';
    const TRANSITION_CANCEL_SHIPPED = 'cancel_shipped';

    public function wasShipped(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_SHIPPED;
    }

    public function canConfirmPayment(OrderEntity $order): bool
    {
        return \in_array($order->getState(), [
            self::STATE_SHIPPED,
            self::STATE_PAID_OUT,
            self::STATE_LATE,
        ], true);
    }

    public function isNew(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_NEW;
    }

    public function isDeclined(OrderEntity $order): bool
    {
        return $order->getState() === self::STATE_DECLINED;
    }
}
