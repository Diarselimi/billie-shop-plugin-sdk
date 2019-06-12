<?php

namespace App\DomainEvent\Order;

class OrderApprovedEvent extends AbstractOrderStateChangeEvent
{
    const NAME = 'order_approved';
}
