<?php

namespace App\DomainModel\Webhook;

use App\DomainModel\Webhook\WebhookClientInterface;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Merchant\MerchantEntity;

class NotificationSender
{
    const ALLOWED_EVENTS = ['reminder', 'dunning1', 'dunning2', 'dunning3', 'rebooking', 'payment', 'dca'];

    private $webhookClient;

    public function __construct(WebhookClientInterface $webhookClient)
    {
        $this->webhookClient = $webhookClient;
    }

    public function send(MerchantEntity $merchant, NotificationDTO $notification)
    {
        if (!$this->isEventAllowed($notification->getEventName())) {
            throw new EventNotAllowedException();
        }

        $this->webhookClient->sendNotification($merchant->getWebhookUrl(), $notification);
    }

    private function isEventAllowed(string $eventName): bool
    {
        return in_array($eventName, self::ALLOWED_EVENTS);
    }
}
