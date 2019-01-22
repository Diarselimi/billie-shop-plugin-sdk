<?php

namespace App\DomainModel\Webhook;

use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;

class NotificationSender implements LoggingInterface
{
    use LoggingTrait;

    private $webhookClient;

    private $sentry;

    public function __construct(WebhookClientInterface $webhookClient, \Raven_Client $sentry)
    {
        $this->webhookClient = $webhookClient;
        $this->sentry = $sentry;
    }

    public function send(MerchantEntity $merchant, NotificationDTO $notification)
    {
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
}
