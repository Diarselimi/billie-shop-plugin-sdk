<?php

namespace App\Http\EventSubscriber;

use App\Application\PaellaCoreCriticalException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        return;
        $exception = $event->getException();

        $responseCode = $exception instanceof PaellaCoreCriticalException && $exception->getResponseCode()
            ? $exception->getResponseCode()
            : JsonResponse::HTTP_BAD_REQUEST
        ;

        $event->setResponse(new JsonResponse([
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
        ], $responseCode));
    }
}
