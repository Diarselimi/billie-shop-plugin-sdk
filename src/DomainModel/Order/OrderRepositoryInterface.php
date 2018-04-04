<?php

namespace App\DomainModel\Order;

interface OrderRepositoryInterface
{
    public function insert(OrderEntity $order): void;
    public function getOneByExternalCode(string $externalCode, int $customerId):? OrderEntity;
}
