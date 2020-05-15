<?php

namespace App\Infrastructure\Nachos;

use App\DomainModel\FileService\FileServiceInterface;
use App\DomainModel\FileService\FileServiceRequestException;
use App\DomainModel\FileService\FileServiceResponseDTO;
use App\DomainModel\FileService\FileServiceResponseFactory;
use App\Infrastructure\DecodeResponseTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\StreamInterface;

class Nachos implements FileServiceInterface
{
    use DecodeResponseTrait;

    private $client;

    private $factory;

    public function __construct(Client $nachosClient, FileServiceResponseFactory $factory)
    {
        $this->client = $nachosClient;
        $this->factory = $factory;
    }

    public function upload(string $contents, string $filename, string $type): FileServiceResponseDTO
    {
        try {
            $response = $this->client->post('files', [
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
            ]);
        } catch (TransferException $exception) {
            throw new FileServiceRequestException($exception);
        }

        return $this->factory->createFromArray($this->decodeResponse($response));
    }

    public function download(string $fileUuid): StreamInterface
    {
        try {
            $response = $this->client->get("files/{$fileUuid}/raw", ['stream' => true]);
        } catch (TransferException $exception) {
            throw new FileServiceRequestException($exception);
        }

        return $response->getBody();
    }
}
