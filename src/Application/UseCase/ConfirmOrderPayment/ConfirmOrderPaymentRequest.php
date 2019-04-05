<?php

namespace App\Application\UseCase\ConfirmOrderPayment;

class ConfirmOrderPaymentRequest
{
    private $orderId;

    private $merchantId;

    private $amount;

    public function __construct(string $orderId, int $merchantId, float $amount)
    {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
        $this->amount = $amount;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}
