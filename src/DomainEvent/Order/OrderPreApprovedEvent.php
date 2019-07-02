<?php

namespace App\DomainEvent\Order;

class OrderPreApprovedEvent extends AbstractOrderStateChangeEvent
{
    const NAME = 'order_pre_approved';
}
