<?php

namespace App\DomainModel\Webhook;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;

class NotificationSender implements LoggingInterface
{
    use LoggingTrait;

    const ALLOWED_EVENTS = [
        NotificationDTO::EVENT_REMINDER,
        NotificationDTO::EVENT_DUNNING1,
        NotificationDTO::EVENT_DUNNING2,
        NotificationDTO::EVENT_DUNNING3,
        NotificationDTO::EVENT_REBOOKING,
        NotificationDTO::EVENT_PAYMENT,
        NotificationDTO::EVENT_DCA
    ];

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
            $this->webhookClient->sendNotification(
                $merchant->getWebhookUrl(),
                $merchant->getWebhookAuthorization(),
                $notification
            );
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
