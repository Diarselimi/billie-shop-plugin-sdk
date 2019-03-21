<?php

namespace App\DomainModel\OrderNotification;

interface NotificationPublisherInterface
{
    public function publish(array $payload, string $interval): bool;
}
