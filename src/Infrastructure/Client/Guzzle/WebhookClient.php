<?php

namespace App\Infrastructure\Client\Guzzle;

use App\DomainModel\Webhook\WebhookClientInterface;
use App\DomainModel\Webhook\NotificationDTO;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use GuzzleHttp\Client;

class WebhookClient implements WebhookClientInterface, LoggingInterface
{
    use LoggingTrait;

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function sendNotification(string $url, NotificationDTO $notification): void
    {
        $data = [
            'event' => $notification->getEventName(),
            'order_id' => $notification->getOrderId(),
        ];

        if (!is_null($notification->getAmount())) {
            $data['amount'] = $notification->getAmount();
        }

        if (!is_null($notification->getOpenAmount())) {
            $data['open_amount'] = $notification->getOpenAmount();
        }

        if (!is_null($notification->getUrlNotification())) {
            $data['url_notification'] = $notification->getUrlNotification();
        }

        $this->logInfo('Webhook request', [
            'url' => $url,
            'request' => $data
        ]);

        $this->client->post($url, $data);
    }
}
