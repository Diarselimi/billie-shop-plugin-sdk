<?php

namespace App\Infrastructure\OrderNotification;

use App\Amqp\Producer\DelayedMessageProducer;
use App\DomainModel\OrderNotification\NotificationPublisherInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class NotificationPublisher implements NotificationPublisherInterface, LoggingInterface
{
    use LoggingTrait;

    private const ROUTING_KEY = 'notification_delivery_paella';

    private $delayedMessageProducer;

    public function __construct(DelayedMessageProducer $delayedMessageProducer)
    {
        $this->delayedMessageProducer = $delayedMessageProducer;
    }

    public function publish(array $payload, string $interval): bool
    {
        return $this->delayedMessageProducer->produce(
            self::ROUTING_KEY,
            $payload,
            $interval
        );
    }
}
