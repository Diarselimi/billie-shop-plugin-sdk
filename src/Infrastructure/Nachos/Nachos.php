<?php

namespace App\Infrastructure\Nachos;

use App\DomainModel\FileService\FileServiceDownloadResponse;
use App\DomainModel\FileService\FileServiceInterface;
use App\DomainModel\FileService\FileServiceRequestException;
use App\DomainModel\FileService\FileServiceUploadResponse;
use App\DomainModel\FileService\FileServiceUploadResponseFactory;
use App\DomainModel\FileService\FileSizeExceededException;
use App\Infrastructure\DecodeResponseTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Nachos implements FileServiceInterface
{
    use DecodeResponseTrait;

    private $client;

    private $factory;

    private Client $urlDownloaderClient;

    public function __construct(
        Client $urlDownloaderClient,
        Client $nachosClient,
        FileServiceUploadResponseFactory $factory
    ) {
        $this->client = $nachosClient;
        $this->factory = $factory;
        $this->urlDownloaderClient = $urlDownloaderClient;
    }

    public function upload(string $contents, string $filename, string $type): FileServiceUploadResponse
    {
        try {
            $response = $this->client->post(
                'files',
                [
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => $contents,
                            'filename' => $filename,
                        ],
                    ],
                    'query' => [
                        'type' => $type,
                    ],
                ]
            );
        } catch (TransferException $exception) {
            throw new FileServiceRequestException($exception);
        }

        return $this->factory->createFromArray($this->decodeResponse($response));
    }

    public function download(string $fileUuid): FileServiceDownloadResponse
    {
        try {
            $response = $this->client->get("files/{$fileUuid}/raw");
        } catch (TransferException $exception) {
            throw new FileServiceRequestException($exception);
        }

        $body = $response->getBody();
        $body->rewind();

        return new FileServiceDownloadResponse(
            $body,
            $response->getHeader('Content-type')[0]
        );
    }

    public function uploadFromFile(UploadedFile $uploadedFile, string $filename, string $type): FileServiceUploadResponse
    {
        try {
            $response = $this->client->post(
                'files',
                [
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => fopen($uploadedFile->getPathname(), 'r'),
                            'filename' => $filename,
                        ],
                    ],
                    'query' => [
                        'type' => $type,
                    ],
                ]
            );
        } catch (TransferException $exception) {
            throw new FileServiceRequestException($exception);
        }

        return $this->factory->createFromArray($this->decodeResponse($response));
    }

    public function uploadFromUrl(
        string $url,
        string $filename,
        string $type,
        int $fileSizeLimit
    ): FileServiceUploadResponse {
        $response = $this->urlDownloaderClient->head($url);

        $size = $response->getHeader('Content-Length');
        if (!isset($size[0]) || $size[0] > $fileSizeLimit) {
            throw new FileSizeExceededException();
        }

        $response = $this->urlDownloaderClient->get($url);

        return $this->upload((string) $response->getBody(), $filename, $type);
    }
}
