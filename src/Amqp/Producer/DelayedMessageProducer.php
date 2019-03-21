<?php

namespace App\Amqp\Producer;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class DelayedMessageProducer implements LoggingInterface
{
    use LoggingTrait;

    private $delayedProducer;

    public function __construct(ProducerInterface $delayedProducer)
    {
        $this->delayedProducer = $delayedProducer;
    }

    public function produce(string $routingKey, array $payload, string $interval): bool
    {
        $delayInMilliseconds = ((new \DateTime($interval))->getTimestamp() - (new \DateTime())->getTimestamp()) * 1000;
        $payload = json_encode($payload);

        try {
            $this->delayedProducer->publish(
                $payload,
                $routingKey,
                [],
                ['x-delay' => $delayInMilliseconds]
            );

            return true;
        } catch (\Exception $exception) {
            $this->logSuppressedException($exception, '[suppressed] Rabbit producer exception', [
                'exception' => $exception,
                'data' => $payload,
            ]);
        }

        return false;
    }
}
