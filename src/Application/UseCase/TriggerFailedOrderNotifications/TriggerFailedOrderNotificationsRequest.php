<?php

namespace App\Application\UseCase\TriggerFailedOrderNotifications;

class TriggerFailedOrderNotificationsRequest
{
    private $uuid;

    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
