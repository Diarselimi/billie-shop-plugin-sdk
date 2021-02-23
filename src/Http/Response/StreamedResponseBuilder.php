<?php

declare(strict_types=1);

namespace App\Http\Response;

use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamedResponseBuilder
{
    private const DEFAULT_CHUNK_SIZE_BYTES = 1024;

    public function build(Request $request, string $fileName, StreamInterface $stream, array $headers, int $chunkSize = null): StreamedResponse
    {
        $streamingCallback = function () use ($stream) {
            do {
                $content = $stream->read($chunkSize ?? static::DEFAULT_CHUNK_SIZE_BYTES);

                echo $content;
                flush();
            } while (!$stream->eof());
        };

        $streamedResponse = new StreamedResponse(
            $streamingCallback,
            StreamedResponse::HTTP_OK,
            $headers
        );

        $streamedResponse->headers->set('Content-Disposition', $streamedResponse->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        ));

        return $streamedResponse->prepare($request);
    }
}
