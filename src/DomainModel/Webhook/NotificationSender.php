<?php

namespace App\DomainModel\Webhook;

use App\DomainModel\Merchant\MerchantEntity;

class NotificationSender
{
    const EVENT_REMINDER = 'reminder';
    const EVENT_DUNNING1 = 'dunning1';
    const EVENT_DUNNING2 = 'dunning2';
    const EVENT_DUNNING3 = 'dunning3';
    const EVENT_REBOOKING = 'rebooking';
    const EVENT_PAYMENT = 'payment';
    const EVENT_DCA = 'dca';

    const ALLOWED_EVENTS = [self::EVENT_REMINDER, self::EVENT_DUNNING1, self::EVENT_DUNNING2, self::EVENT_DUNNING3, self::EVENT_REBOOKING, self::EVENT_PAYMENT, self::EVENT_DCA];

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
