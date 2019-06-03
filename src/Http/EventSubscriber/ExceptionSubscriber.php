<?php

namespace App\Http\EventSubscriber;

use App\Application\Exception\RequestValidationException;
use App\Application\PaellaCoreCriticalException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ExceptionSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private $camelCaseToSnakeCaseNameConverter;

    public function __construct(CamelCaseToSnakeCaseNameConverter $camelCaseToSnakeCaseNameConverter)
    {
        $this->camelCaseToSnakeCaseNameConverter = $camelCaseToSnakeCaseNameConverter;
    }

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

        if ($exception instanceof RequestValidationException) {
            /** @var ConstraintViolationInterface $validationError */
            foreach ($exception->getValidationErrors() as $validationError) {
                $errors[] = [
                    'source' => $this->camelCaseToSnakeCaseNameConverter->normalize($validationError->getPropertyPath()),
                    'title' => $validationError->getMessage(),
                    'code' => $exception->getMessage(),
                ];
            }

            $event->setResponse(new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST));

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

        if ($previousException instanceof RequestException && $previousException->getResponse()) {
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
