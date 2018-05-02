<?php

namespace App\Http\EventSubscriber;

use App\Application\PaellaCoreCriticalException as Exception;
use App\Http\HttpConstantsInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestCheckSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->getRequest()->headers->has(HttpConstantsInterface::REQUEST_HEADER_API_USER)) {
            throw new Exception('User header is missing', Exception::CODE_USER_HEADER_MISSING);
        }
    }
}
