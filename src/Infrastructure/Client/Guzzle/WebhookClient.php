<?php

namespace App\Infrastructure\Client\Guzzle;

use App\DomainModel\Webhook\WebhookClientInterface;
use App\DomainModel\Webhook\NotificationDTO;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Webhook\WebhookCommunicationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

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

        $extraData = array_filter([
            'amount' => $notification->getAmount(),
            'open_amount' => $notification->getOpenAmount(),
            'url_notification' => $notification->getUrlNotification(),
        ]);

        $data = array_merge($data, $extraData);

        $this->logInfo('Webhook request', [
            'url' => $url,
            'request' => $data
        ]);

        try {
            $this->client->post($url, $data);
        } catch (TransferException $exception) {
            throw new WebhookCommunicationException('Webhook communication exception', null, $exception);
        }
    }
}
