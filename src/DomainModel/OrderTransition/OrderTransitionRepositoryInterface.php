<?php

namespace App\DomainModel\OrderTransition;

interface OrderTransitionRepositoryInterface
{
    public function insert(OrderTransitionEntity $orderTransition): void;
}
