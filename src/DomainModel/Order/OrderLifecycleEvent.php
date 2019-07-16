<?php

namespace App\DomainModel\Order;

use Symfony\Component\EventDispatcher\Event;

class OrderLifecycleEvent extends Event
{
    const UPDATED = 'paella_core.order.update';

    const CREATED = 'paella_core.order.created';

    private $order;

    public function __construct(OrderEntity $order)
    {
        $this->order = $order;
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }
}
