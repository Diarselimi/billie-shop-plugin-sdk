<?php

namespace App\DomainEvent\Order;

class OrderInWaitingStateEvent extends AbstractOrderStateChangeEvent
{
    const NAME = 'order_in_waiting_state';
}
