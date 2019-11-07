<?php

namespace App\Http\ApiError;

use App\Application\Exception\RequestValidationException;
use App\Application\PaellaCoreCriticalException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ApiErrorResponseFactory
{
    private $propertyNameConverter;

    public function __construct(CamelCaseToSnakeCaseNameConverter $propertyNameConverter)
    {
        $this->propertyNameConverter = $propertyNameConverter;
    }

    public function createFromException(\Exception $exception): ApiErrorResponse
    {
        switch (true) {
            case $exception instanceof RequestValidationException:
                return $this->createFromRequestValidationException($exception);
            case $exception instanceof HttpException:
                return $this->createFromHttpException($exception);
            case $exception instanceof PaellaCoreCriticalException:
                return $this->createFromPaellaCriticalException($exception);
            default:
                return $this->createResponse(
                    [$this->createDebugError($exception)],
                    ApiErrorResponse::HTTP_INTERNAL_SERVER_ERROR
                );
        }
    }

    private function createDebugError(\Exception $exception): ApiError
    {
        $additionalData = [
            'stack_trace' => $exception->getTraceAsString(),
        ];

        return new ApiError(
            $exception->getMessage(),
            get_class($exception) . ':' . $exception->getCode(),
            $exception->getFile() . ':' . $exception->getLine(),
            $additionalData
        );
    }

    private function createFromRequestValidationException(RequestValidationException $exception): ApiErrorResponse
    {
        $errors = [];

        /** @var ConstraintViolationInterface $validationError */
        foreach ($exception->getValidationErrors() as $validationError) {
            $errors[] = new ApiError(
                $validationError->getMessage(),
                ApiError::CODE_REQUEST_VALIDATION_ERROR,
                $this->propertyNameConverter->normalize($validationError->getPropertyPath())
            );
        }

        return $this->createResponse($errors, ApiErrorResponse::HTTP_BAD_REQUEST);
    }

    private function createFromHttpException(HttpException $exception): ApiErrorResponse
    {
        $message = $exception->getMessage();

        switch (true) {
            case $exception instanceof NotFoundHttpException:
                $errorCode = ApiError::CODE_RESOURCE_NOT_FOUND;

                break;
            case $exception instanceof AccessDeniedHttpException:
                if (stripos($message, '@IsGranted') !== false) {
                    // Hide default IsGranted annotation messages
                    $message = 'Access Denied.';
                }
                $errorCode = ApiError::CODE_FORBIDDEN;

                break;
            case $exception instanceof UnauthorizedHttpException:
                $errorCode = ApiError::CODE_UNAUTHORIZED;

                break;
            case $exception instanceof BadRequestHttpException:
                $errorCode = ApiError::CODE_REQUEST_INVALID;

                break;
            case $exception instanceof ServiceUnavailableHttpException:
                $errorCode = ApiError::CODE_SERVICE_UNAVAILABLE;

                break;
            default:
                $errorCode = ApiError::CODE_OPERATION_FAILED;

                break;
            case $exception instanceof ConflictHttpException:
                $errorCode = ApiError::CODE_RESOURCE_CONFLICT;

                break;
        }

        return $this->createResponse(
            [new ApiError($message, $errorCode)],
            $exception->getStatusCode(),
            $exception->getHeaders()
        );
    }

    private function createFromPaellaCriticalException(PaellaCoreCriticalException $exception)
    {
        $statusCode = $exception->getResponseCode() ? $exception->getResponseCode() : ApiErrorResponse::HTTP_INTERNAL_SERVER_ERROR;

        return $this->createResponse([new ApiError($exception->getMessage(), $exception->getErrorCode())], $statusCode);
    }

    protected function createResponse(array $errors, int $statusCode, array $headers = []): ApiErrorResponse
    {
        if ($statusCode === 500) {
            // hide real message of uncaught exceptions
            return new ApiErrorResponse(
                [new ApiError('Unexpected Internal Error', ApiError::CODE_INTERNAL_ERROR)],
                $statusCode,
                $headers
            );
        }

        return new ApiErrorResponse($errors, $statusCode, $headers);
    }
}
