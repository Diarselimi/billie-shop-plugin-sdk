<?php

namespace App\DomainEvent\Order;

class OrderCompleteEvent extends AbstractOrderStateChangeEvent
{
    const NAME = 'order_complete';
}
