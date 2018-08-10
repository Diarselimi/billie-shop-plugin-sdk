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

    public function sendNotification(string $url, ?string $authorisation, NotificationDTO $notification): void
    {
        $data = [
            'event' => $notification->getEventName(),
            'order_id' => $notification->getOrderId(),
        ];

        if ($notification->isEventTypePayment()) {
            $data['amount'] = $notification->getAmount();
            $data['open_amount'] = $notification->getOpenAmount();
        } else {
            $data['url_notification'] = $notification->getUrlNotification();
        }

        $this->logInfo('Webhook request', [
            'url' => $url,
            'request' => $data
        ]);

        $headers = ['Content-Type' => 'application/json'];
        if ($authorisation) {
            $headers['X-Api-Key'] = $authorisation;
        }

        try {
            $this->client->post($url, [
                'json' => $data,
                'headers' => $headers,
            ]);
        } catch (TransferException $exception) {
            throw new WebhookCommunicationException('Webhook communication exception', null, $exception);
        }
    }
}
