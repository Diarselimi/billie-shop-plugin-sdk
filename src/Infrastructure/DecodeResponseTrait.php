<?php

namespace App\Infrastructure;

use Psr\Http\Message\ResponseInterface;

trait DecodeResponseTrait
{
    protected function decodeResponse(ResponseInterface $response): array
    {
        $response = (string) $response->getBody();
        $decodedResponse = json_decode($response, true);

        if (json_last_error() > 0) {
            throw new ClientResponseDecodeException();
        }

        return $decodedResponse;
    }
}
