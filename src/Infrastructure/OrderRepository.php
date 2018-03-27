<?php

namespace App\Infrastructure;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function insert(OrderEntity $order): void
    {
    }

    public function getOneByExternalCode(string $externalCode): OrderEntity
    {
        return (new OrderEntity())
            ->setId(43)
            ->setAmount(500)
        ;
    }

    public function getOneByExternalCodeRaw(string $externalCode): array
    {
        return [
            'id' => 15,
            'amount' => 1000,
            'duration' => 30,
        ];
    }
}
