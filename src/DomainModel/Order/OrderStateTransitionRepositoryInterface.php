<?php

namespace App\DomainModel\Order;

interface OrderStateTransitionRepositoryInterface
{
    public function insert(OrderStateTransitionEntity $transition): void;
}
