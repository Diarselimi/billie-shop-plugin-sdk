<?php

namespace App\Application\UseCase\TriggerFailedOrderNotifications;

class TriggerFailedOrderNotificationsRequest
{
    private $orderExternalCode;

    private $merchantId;

    public function __construct(string $orderExternalCode, int $merchantId)
    {
        $this->orderExternalCode = $orderExternalCode;
        $this->merchantId = $merchantId;
    }

    public function getOrderExternalCode(): string
    {
        return $this->orderExternalCode;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
