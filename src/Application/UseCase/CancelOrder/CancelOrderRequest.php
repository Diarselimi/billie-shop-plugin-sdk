<?php

namespace App\Application\UseCase\CancelOrder;

class CancelOrderRequest
{
    private string $orderId;

    private ?int $merchantId;

    public function __construct(string $orderId, ?int $merchantId = null)
    {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }
}
