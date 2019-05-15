<?php

namespace App\DomainEvent\Order;

class OrderDeclinedEvent extends AbstractOrderStateChangeEvent
{
    const NAME = 'order_declined';
}
