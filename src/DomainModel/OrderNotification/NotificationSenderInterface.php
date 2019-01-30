<?php

namespace App\DomainModel\OrderNotification;

interface NotificationSenderInterface
{
    public function send(string $url, ?string $authorisation, array $data): NotificationDeliveryResultDTO;
}
