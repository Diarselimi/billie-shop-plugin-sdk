<?php

namespace App\Http\EventSubscriber;

use Billie\MonitoringBundle\Service\RidProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RidResponseSubscriber implements EventSubscriberInterface
{
    private RidProvider $ridProvider;

    public function __construct(RidProvider $ridProvider)
    {
        $this->ridProvider = $ridProvider;
    }

    public function onResponse(ResponseEvent $event)
    {
        $event->getResponse()->headers->add(['X-Request-Id' => $this->ridProvider->getRid()]);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }
}
