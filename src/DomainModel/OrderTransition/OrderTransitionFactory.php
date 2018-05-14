<?php

namespace App\DomainModel\OrderTransition;

class OrderTransitionFactory
{
    public function create(int $orderId, string $transition): OrderTransitionEntity
    {
        return (new OrderTransitionEntity())
            ->setOrderId($orderId)
            ->setTransition($transition)
            ->setTransitedAt(new \DateTime())
        ;
    }
}
