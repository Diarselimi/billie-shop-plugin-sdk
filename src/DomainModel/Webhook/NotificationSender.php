<?php

namespace App\DomainModel\Webhook;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;

class NotificationSender implements LoggingInterface
{
    use LoggingTrait;

    const EVENT_REMINDER = 'reminder';
    const EVENT_DUNNING1 = 'dunning1';
    const EVENT_DUNNING2 = 'dunning2';
    const EVENT_DUNNING3 = 'dunning3';
    const EVENT_REBOOKING = 'rebooking';
    const EVENT_PAYMENT = 'payment';
    const EVENT_DCA = 'dca';

    const ALLOWED_EVENTS = [self::EVENT_REMINDER, self::EVENT_DUNNING1, self::EVENT_DUNNING2, self::EVENT_DUNNING3, self::EVENT_REBOOKING, self::EVENT_PAYMENT, self::EVENT_DCA];

    private $webhookClient;
    private $sentry;

    public function __construct(WebhookClientInterface $webhookClient, \Raven_Client $sentry)
    {
        $this->webhookClient = $webhookClient;
        $this->sentry = $sentry;
    }

    public function send(MerchantEntity $merchant, NotificationDTO $notification)
    {
        if (!$this->isEventAllowed($notification->getEventName())) {
            throw new EventNotAllowedException();
        }

        if ($merchant->getWebhookUrl() === null) {
            return;
        }

        try {
            $this->webhookClient->sendNotification($merchant->getWebhookUrl(), $notification);
        } catch (WebhookCommunicationException $exception) {
            $this->logError('[suppressed] Webhook exception', [
                'exception' => $exception,
                'notification' => $notification,
            ]);

            $this->sentry->captureException($exception);
        }
    }

    private function isEventAllowed(string $eventName): bool
    {
        return in_array($eventName, self::ALLOWED_EVENTS);
    }
}
