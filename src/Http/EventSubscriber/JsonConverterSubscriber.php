<?php

namespace App\Http\EventSubscriber;

use App\Application\PaellaCoreCriticalException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonConverterSubscriber implements EventSubscriberInterface
{
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PATCH])
            || !$request->headers->has('Content-Type')
            || !$request->headers->get('Content-Type') === 'application/json'
            || !$request->getContent()
        ) {
            return;
        }

        $json = $request->getContent();
        $requestData = json_decode($json, true);

        if (!$requestData) {
            throw new PaellaCoreCriticalException(
                "Request couldn't be decoded",
                PaellaCoreCriticalException::CODE_REQUEST_DECODE_EXCEPTION
            );
        }

        $request->request->add($requestData);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }
}
