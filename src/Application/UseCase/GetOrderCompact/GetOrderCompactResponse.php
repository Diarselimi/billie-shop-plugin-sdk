<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetOrderCompact;

use App\DomainModel\Order\OrderContainer\OrderContainer;

final class GetOrderCompactResponse
{
    private OrderContainer $orderContainer;

    public function __construct(OrderContainer $orderContainer)
    {
        $this->orderContainer = $orderContainer;
    }

    public function getOrderContainer(): OrderContainer
    {
        return $this->orderContainer;
    }
}
