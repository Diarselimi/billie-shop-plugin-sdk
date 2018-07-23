<?php

namespace App\Infrastructure\Monitoring\Sentry;

use App\Application\PaellaCoreCriticalException;
use App\Infrastructure\Monitoring\RidProvider;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SentrySubscriber implements EventSubscriberInterface
{
    private $client;
    private $ridProvider;
    private $instance;

    public function __construct(\Raven_Client $client, RidProvider $ridProvider, string $env)
    {
        $this->client = $client;
        $this->ridProvider = $ridProvider;
        $this->instance = substr($env, 1);

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

        $this->captureException($exception);
    }

    public function onConsoleHttpException(ConsoleErrorEvent $event)
    {
        $error = $event->getError();

        if ($this->isIgnored($error)) {
            return;
        }

        $this->captureException($error);
    }

    private function isIgnored(\Throwable $throwable): bool
    {
        if ($throwable instanceof PaellaCoreCriticalException) {
            return true;
        }

        $skipCapture = [];

        foreach ($skipCapture as $className) {
            if ($throwable instanceof $className) {
                return true;
            }
        }

        return false;
    }

    private function captureException(\Throwable $exception)
    {
        $this->client->captureException($exception, [
            'tags' => [
                'rid' => $this->ridProvider->getRid(),
                'instance' => $this->instance,
            ],
        ]);
    }
}
