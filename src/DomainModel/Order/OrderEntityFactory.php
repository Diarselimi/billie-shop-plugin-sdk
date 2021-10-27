<?php

namespace App\DomainModel\Order;

interface OrderEntityFactory
{
    public function createFromRows(iterable $arrays): array;

    public function createCollection(iterable $arrays): OrderCollection;

    public function create(array $row): OrderEntity;
}
