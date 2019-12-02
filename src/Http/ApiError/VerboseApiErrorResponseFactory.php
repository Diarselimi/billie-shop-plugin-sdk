<?php

namespace App\Http\ApiError;

class VerboseApiErrorResponseFactory extends ApiErrorResponseFactory
{
    protected function createGenericError(?\Exception $exception = null): ApiError
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
}
