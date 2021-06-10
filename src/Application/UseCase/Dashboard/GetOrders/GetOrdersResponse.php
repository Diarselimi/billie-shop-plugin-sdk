<?php

namespace App\Application\UseCase\Dashboard\GetOrders;

use App\DomainModel\Order\Aggregate\OrderAggregateCollection;

class GetOrdersResponse
{
    private OrderAggregateCollection $collection;

    private int $totalCount;

    public function __construct(OrderAggregateCollection $collection, int $totalCount)
    {
        $this->collection = $collection;
        $this->totalCount = $totalCount;
    }

    public function getCollection(): OrderAggregateCollection
    {
        return $this->collection;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
