<?php

namespace App\DomainModel\OrderNotification;

interface NotificationPublisherInterface
{
    public function publish(string $payload, \DateInterval $delay): bool;
}
