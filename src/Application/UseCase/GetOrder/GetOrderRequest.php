<?php

namespace App\Application\UseCase\GetOrder;

class GetOrderRequest
{
    private $orderId;

    private $merchantId;

    public function __construct(string $orderId, int $merchantId)
    {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
