<?php

namespace App\Application\UseCase\GetOrders;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\OrderResponse\OrderResponseV1;

class GetOrdersResponse implements ArrayableInterface
{
    private $orders;

    private $totalCount;

    public function __construct(int $totalCount, OrderResponseV1 ...$orders)
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
            'items' => array_map(function (OrderResponseV1 $orderResponse) {
                return $orderResponse->toArray();
            }, $this->getOrders()),
        ];
    }
}
