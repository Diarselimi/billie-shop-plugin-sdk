<?php

namespace App\Application\UseCase\ConfirmOrder;

class ConfirmOrder
{
    private string $orderId;

    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    public function orderId(): string
    {
        return $this->orderId;
    }
}
