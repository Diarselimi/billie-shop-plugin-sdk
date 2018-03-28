<?php

namespace App\DomainModel\Order;

interface OrderRepositoryInterface
{
    public function insert(array $order): void;
    public function getOneByExternalCode(string $externalCode):? OrderEntity;
    public function getOneByExternalCodeRaw(string $externalCode):? array;
}
