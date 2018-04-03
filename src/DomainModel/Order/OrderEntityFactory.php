<?php

namespace App\DomainModel\Order;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;

class OrderEntityFactory
{
    public function createFromRequest(CreateOrderRequest $request): OrderEntity
    {
        return (new OrderEntity())
            ->setAmount($request->getAmount())
            ->setDuration($request->getDuration())
            ->setExternalComment($request->getComment())
            ->setExternalCode($request->getExternalCode())
            ->setState(OrderEntity::STATE_NEW)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
        ;
    }

    public function createFromArray(array $order)
    {

    }
}
