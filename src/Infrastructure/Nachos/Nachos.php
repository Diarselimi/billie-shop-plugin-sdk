<?php

namespace App\Infrastructure\Nachos;

use App\DomainModel\FileService\FileServiceInterface;
use App\DomainModel\FileService\FileServiceResponseDTO;
use App\DomainModel\FileService\FileServiceResponseFactory;
use App\Infrastructure\DecodeResponseTrait;
use Aws\Transfer\Exception\TransferException;
use GuzzleHttp\Client;

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

    public function upload(string $contents, string $filename): FileServiceResponseDTO
    {
        try {
            $response = $this->client->post('/files', [
                'multipart' => [[
                    'name' => 'file',
                    'contents' => $contents,
                    'filename' => $filename,
                ]],
            ]);
        } catch (TransferException $exception) {
            throw new NachosCommunicationException('Nachos communication exception', null, $exception);
        }

        return $this->factory->createFromArray($this->decodeResponse($response));
    }
}
