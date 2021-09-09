<?php

namespace App\Amqp\Producer;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class DelayedMessageProducer implements LoggingInterface
{
    use LoggingTrait;

    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function produce($message, string $interval): void
    {
        $delayInMilliseconds = ((new \DateTime($interval))->getTimestamp() - (new \DateTime())->getTimestamp()) * 1000;

        try {
            $this->bus->dispatch(
                $message,
                [
                    new DelayStamp($delayInMilliseconds),
                ]
            );
        } catch (\Exception $exception) {
            $this->logSuppressedException($exception, '[suppressed] Rabbit producer exception', [
                'exception' => $exception,
            ]);
        }
    }
}
