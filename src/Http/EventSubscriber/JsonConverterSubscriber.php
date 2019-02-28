<?php

namespace App\Http\EventSubscriber;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\ArrayableInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
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

    public function onView(GetResponseForControllerResultEvent $event)
    {
        $response = $event->getControllerResult();

        if ($response === null) {
            $event->setResponse(new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT));
        }

        if ($response instanceof ArrayableInterface) {
            $event->setResponse(new JsonResponse($response->toArray()));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            KernelEvents::VIEW => 'onView',
        ];
    }
}
