<?php

namespace App\DomainModel\Webhook;

interface WebhookClientInterface
{
    public function sendNotification(string $url, ?string $authorisation, NotificationDTO $notification): void;
}
