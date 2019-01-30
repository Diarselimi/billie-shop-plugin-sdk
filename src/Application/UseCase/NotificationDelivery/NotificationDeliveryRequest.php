<?php

namespace App\Application\UseCase\NotificationDelivery;

class NotificationDeliveryRequest
{
    private $orderNotificationId;

    public function __construct(int $orderNotificationId)
    {
        $this->orderNotificationId = $orderNotificationId;
    }

    public function getOrderNotificationId(): int
    {
        return $this->orderNotificationId;
    }
}
