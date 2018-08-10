<?php

namespace App\Infrastructure\Monitoring;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestLogSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    public function onRequest(GetResponseEvent $event)
    {
        $ignoredRoutes = ['health_check'];
        $request = $event->getRequest();
        $route = $request->get('_route');

        if (\in_array($route, $ignoredRoutes, true)) {
            return;
        }

        $this->logInfo('Request to {route} received', [
            'route' => $route,
            'url' => $request->getUri(),
            'body' => $request->getContent(),
        ]);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 9],
        ];
    }
}
