<?php

declare(strict_types=1);

namespace App\DomainModel\Order\DomainEvent;

use Ozean12\AmqpPackBundle\DomainEvent;

class NotificationDeliveryDomainEvent extends DomainEvent
{
    private int $notificationId;

    public function __construct(int $notificationId)
    {
        $this->notificationId = $notificationId;
    }

    public function getNotificationId(): int
    {
        return $this->notificationId;
    }
}
