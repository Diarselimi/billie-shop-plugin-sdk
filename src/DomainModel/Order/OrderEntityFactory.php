<?php

namespace App\DomainModel\Order;

class OrderEntityFactory
{
    public function create(float $amount, int $duration)
    {
        return (new OrderEntity())
            ->setAmount($amount)
            ->setDuration($duration)
        ;
    }
}
