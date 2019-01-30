<?php

namespace App\Infrastructure\OrderNotification;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\OrderNotification\NotificationPublisherInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class NotificationPublisher implements NotificationPublisherInterface, LoggingInterface
{
    use LoggingTrait;

    private const ROUTING_KEY = 'notification_delivery_paella';

    private $delayedProducer;

    public function __construct(ProducerInterface $delayedProducer)
    {
        $this->delayedProducer = $delayedProducer;
    }

    public function publish(string $payload, \DateInterval $delay): bool
    {
        try {
            $this->delayedProducer->publish(
                $payload,
                self::ROUTING_KEY,
                [],
                ['x-delay' => $delay->format('f')]
            );
        } catch (\ErrorException $exception) {
            $this->logSuppressedException($exception, '[suppressed] Rabbit producer exception', [
                'exception' => $exception,
                'data' => $payload,
            ]);

            return false;
        }

        return true;
    }
}
