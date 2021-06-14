<?php

declare(strict_types=1);

namespace App\DomainModel\Order\Search;

use App\DomainModel\Order\OrderCollection;
use App\DomainModel\Order\OrderEntity;

class OrderSearchResult implements \IteratorAggregate, \Countable
{
    private OrderCollection $orders;

    private int $totalCount;

    public function __construct(OrderCollection $orders, int $totalCount)
    {
        $this->orders = $orders;
        $this->totalCount = $totalCount;
    }

    public function getOrders(): OrderCollection
    {
        return $this->orders;
    }

    public function count(): int
    {
        return $this->getOrders()->count();
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return \ArrayIterator|OrderEntity[]
     */
    public function getIterator(): \ArrayIterator
    {
        return $this->getOrders()->getIterator();
    }
}
