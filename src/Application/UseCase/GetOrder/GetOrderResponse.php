<?php

namespace App\Application\UseCase\GetOrder;

class GetOrderResponse
{
    private $orderData;

    public function __construct(array $orderData)
    {
        $this->orderData = $orderData;
    }

    public function getOrderData(): array
    {
        return $this->orderData;
    }
}
