<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;

class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
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

    public function getOneByExternalCodeRaw(string $externalCode):? array
    {
        $order = $this->fetch('SELECT * FROM orders WHERE external_code = :external_code', [
            'external_code' => $externalCode,
        ]);

        return $order ?: null;
    }
}
