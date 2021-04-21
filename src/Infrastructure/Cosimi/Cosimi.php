<?php

declare(strict_types=1);

namespace App\Infrastructure\Cosimi;

use App\DomainModel\CompanySimilarity\CompanySimilarityServiceException;
use App\DomainModel\CompanySimilarity\CompanySimilarityServiceInterface;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\TransferStats;

class Cosimi implements CompanySimilarityServiceInterface, LoggingInterface
{
    use DecodeResponseTrait;
    use LoggingTrait;

    private $client;

    public function __construct(Client $cosimiClient)
    {
        $this->client = $cosimiClient;
    }

    public function match(array $input, array $candidate): array
    {
        try {
            $response = $this->client->post('/company_similarity', [
                'json' => [
                    'calculate_company_similarities' => [
                        "input_data" => $input,
                        "candidate_data" => [$candidate],
                    ],
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'cosimi');
                },
            ]);

            return $this->decodeResponse($response);
        } catch (GuzzleException $exception) {
            throw new CompanySimilarityServiceException($exception->getMessage(), 0, $exception);
        }
    }
}
