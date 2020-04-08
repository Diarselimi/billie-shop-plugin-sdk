<?php

namespace App\Infrastructure;

use Psr\Http\Message\ResponseInterface;

trait DecodeResponseTrait
{
    protected function decodeResponse(ResponseInterface $response): array
    {
        $responseBody = (string) $response->getBody();
        $decodedResponse = json_decode($responseBody, true);

        if (json_last_error() > 0) {
            throw new ClientResponseDecodeException(
                sprintf("JSON error: '%s' // Response Body:\n '%s'", json_last_error_msg(), $responseBody)
            );
        }

        return $decodedResponse;
    }
}
