<?php

namespace App\DomainModel\OrderTransition;

class OrderTransitionFactory
{
    public function create(int $orderId, ?string $from, string $to, string $transition): OrderTransitionEntity
    {
        return (new OrderTransitionEntity())
            ->setOrderId($orderId)
            ->setFrom($from)
            ->setTo($to)
            ->setTransition($transition)
            ->setTransitedAt(new \DateTime())
        ;
    }
}
