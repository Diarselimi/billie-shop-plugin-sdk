<?php

declare(strict_types=1);

namespace App\DomainModel\FileService;

use Psr\Http\Message\StreamInterface;

class FileServiceDownloadResponse
{
    private string $contentType;

    private StreamInterface $stream;

    public function __construct(StreamInterface $stream, string $contentType)
    {
        $this->contentType = $contentType;
        $this->stream = $stream;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getStream(): StreamInterface
    {
        return $this->stream;
    }
}
