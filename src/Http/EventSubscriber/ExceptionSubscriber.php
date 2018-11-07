<?php

namespace App\Http\EventSubscriber;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        $previousException = $exception->getPrevious();

        if ($exception instanceof HttpException) {
            $event->setResponse(new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode()));

            return;
        }

        $responseCode = $exception instanceof PaellaCoreCriticalException && $exception->getResponseCode()
            ? $exception->getResponseCode()
            : JsonResponse::HTTP_INTERNAL_SERVER_ERROR
        ;

        $errorCode = $exception instanceof PaellaCoreCriticalException
            ? $exception->getErrorCode()
            : $exception->getCode();

        $error = [
            'code' => $errorCode,
            'error' => $exception->getMessage(),
        ];

        if (!($exception instanceof PaellaCoreCriticalException)) {
            $error['stack_trace'] = $exception->getTraceAsString();
        }

        if ($previousException instanceof RequestException) {
            $remoteError = json_decode($previousException->getResponse()->getBody()->__toString(), true);
            if (is_array($remoteError)) {
                if (isset($remoteError['stack_trace'])) {
                    unset($remoteError['stack_trace']);
                }
                if (isset($remoteError['stack'])) {
                    unset($remoteError['stack']);
                }
                $error['remote_error'] = $remoteError;
                $error['remote_error']['status_code'] = $previousException->getResponse()->getStatusCode();
            }
        }

        $this->logError('Critical exception', $error);
        $event->setResponse(new JsonResponse($error, $responseCode));
    }
}
