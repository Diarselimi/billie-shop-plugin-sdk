<?php

namespace App\Infrastructure\SepaB2BGenerator;

use App\DomainModel\SepaB2BGenerator\DocumentGeneratorClientInterface;
use App\DomainModel\SepaB2BGenerator\SepaB2BDocumentGenerationRequestDTO;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;

class DocumentGeneratorClient implements DocumentGeneratorClientInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private $client;

    public function __construct(Client $documentGeneratorClient)
    {
        $this->client = $documentGeneratorClient;
    }

    public function generate(SepaB2BDocumentGenerationRequestDTO $b2BGeneratorDTO): string
    {
        $response = $this->client->post('generate/b2b_mandate', [
            'json' => $b2BGeneratorDTO->toArray(),
        ]);

        return base64_decode((string) $response->getBody());
    }
}
