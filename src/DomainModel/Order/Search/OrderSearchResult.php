<?php

declare(strict_types=1);

namespace App\DomainModel\Order\Search;

use App\DomainModel\Order\Aggregate\OrderAggregateCollection;

class OrderSearchResult
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
