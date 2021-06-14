<?php

namespace App\Application\UseCase\Dashboard\GetOrders;

use App\DomainModel\Order\OrderCollection;

class GetOrdersResponse
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

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
