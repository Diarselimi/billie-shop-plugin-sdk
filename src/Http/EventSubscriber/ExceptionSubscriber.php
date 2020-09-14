<?php

namespace App\Http\EventSubscriber;

use App\Http\ApiError\ApiErrorResponse;
use App\Http\ApiError\ApiErrorResponseFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private $errorResponseFactory;

    public function __construct(ApiErrorResponseFactory $errorResponseFactory)
    {
        $this->errorResponseFactory = $errorResponseFactory;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onException', -1],
            ],
        ];
    }

    public function onException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $response = $this->errorResponseFactory->createFromException($exception);
        $event->setResponse($response);

        if ($response->getStatusCode() < ApiErrorResponse::HTTP_INTERNAL_SERVER_ERROR) {
            return;
        }

        $this->logError('Critical Exception', [LoggingInterface::KEY_SOBAKA => $response->getErrors()]);
        if ($exception->getPrevious()) {
            $prevResponse = $this->errorResponseFactory->createFromException($exception->getPrevious());
            $this->logError(
                'Critical Exception (previous)',
                [LoggingInterface::KEY_SOBAKA => $prevResponse->getErrors()]
            );
        }
    }
}
