<?php

namespace App\Http\ApiError;

class VerboseApiErrorResponseFactory extends ApiErrorResponseFactory
{
    protected function createResponse(array $errors, int $statusCode, array $headers = []): ApiErrorResponse
    {
        return new ApiErrorResponse($errors, $statusCode, $headers);
    }
}
