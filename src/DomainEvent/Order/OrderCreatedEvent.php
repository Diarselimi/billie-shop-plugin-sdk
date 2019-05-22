<?php

namespace App\DomainEvent\Order;

class OrderCreatedEvent extends AbstractOrderStateChangeEvent
{
    const NAME = 'order_created';
}
