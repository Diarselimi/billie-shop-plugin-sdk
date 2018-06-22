<?php

namespace App\DomainModel\Webhook;

interface WebhookClientInterface
{
    public function sendNotification(string $url, NotificationDTO $notification): void;
}
