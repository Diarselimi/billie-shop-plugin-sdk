<?php

namespace App\Application\UseCase\CreateOrder;

class CreateOrderResponse
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
