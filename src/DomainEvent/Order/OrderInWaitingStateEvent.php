<?php

namespace App\DomainEvent\Order;

use App\DomainModel\Order\OrderEntity;
use Symfony\Component\EventDispatcher\Event;

class OrderInWaitingStateEvent extends Event
{
    const NAME = 'order_in_waiting_state';

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
