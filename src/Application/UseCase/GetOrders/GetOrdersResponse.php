<?php

namespace App\Application\UseCase\GetOrders;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\OrderResponse\OrderResponse;

class GetOrdersResponse implements ArrayableInterface
{
    private $orders;

    private $totalCount;

    public function __construct(int $totalCount, OrderResponse ...$orders)
    {
        $this->totalCount = $totalCount;
        $this->orders = $orders;
    }

    public function getOrders(): array
    {
        return $this->orders;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->getTotalCount(),
            'items' => array_map(function (OrderResponse $orderResponse) {
                return $orderResponse->toArray();
            }, $this->getOrders()),
        ];
    }
}
