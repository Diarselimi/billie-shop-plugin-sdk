<?php

namespace App\Http\EventSubscriber;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $responseCode = $exception instanceof PaellaCoreCriticalException && $exception->getResponseCode()
            ? $exception->getResponseCode()
            : JsonResponse::HTTP_BAD_REQUEST
        ;

        $errorCode = $exception instanceof PaellaCoreCriticalException
            ? $exception->getErrorCode()
            : $exception->getCode()
        ;

        $error = [
            'code' => $errorCode,
            'message' => $exception->getMessage(),
            'stack' => $exception->getTraceAsString(),
        ];

        $this->logError('Critical exception', $error);
        $event->setResponse(new JsonResponse($error, $responseCode));
    }
}
