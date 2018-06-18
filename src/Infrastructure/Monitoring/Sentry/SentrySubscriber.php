<?php

namespace App\Infrastructure\Monitoring\Sentry;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SentrySubscriber implements EventSubscriberInterface
{
    /**
     * @var \Raven_Client
     */
    protected $client;

    public function __construct(\Raven_Client $client)
    {
        $this->client = $client;
        $serializers = [
            new \Raven_DefaultObjectSerializer(),
            new SentryPrimaryKeySerializer(),
        ];

        foreach ($serializers as $serializer) {
            $this->client->getSerializer()->getObjectSerializer()->addSerializer($serializer);
            $this->client->getReprSerializer()->getObjectSerializer()->addSerializer($serializer);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelHttpException', 100],
            ConsoleEvents::ERROR => ['onConsoleHttpException', 100],
            KernelEvents::REQUEST => 'onKernelHttpRequest',
        ];
    }

    public function onKernelHttpRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }
    }

    public function onKernelHttpException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($this->isIgnored($exception)) {
            return;
        }

        $this->client->captureException($exception);
    }

    public function onConsoleHttpException(ConsoleErrorEvent $event)
    {
        $error = $event->getError();

        if ($this->isIgnored($error)) {
            return;
        }

        $this->client->captureException($error);
    }

    private function isIgnored(\Throwable $throwable): bool
    {
        $skipCapture = [];

        foreach ($skipCapture as $className) {
            if ($throwable instanceof $className) {
                return true;
            }
        }

        return false;
    }
}
