<?php

declare(strict_types=1);

namespace App\Infrastructure\Webapp;

use App\DomainModel\IdentityVerification\IdentityVerificationServiceException;
use App\DomainModel\IdentityVerification\IdentityVerificationServiceInterface;
use App\DomainModel\IdentityVerification\IdentityVerificationStartRequestDTO;
use App\DomainModel\IdentityVerification\IdentityVerificationStartResponseDTO;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;

class WebappApiClient implements IdentityVerificationServiceInterface, LoggingInterface
{
    use DecodeResponseTrait, LoggingTrait;

    private $client;

    public function __construct(Client $webappClient)
    {
        $this->client = $webappClient;
    }

    public function startVerificationCase(IdentityVerificationStartRequestDTO $requestDTO): IdentityVerificationStartResponseDTO
    {
        try {
            $response = $this->client->post('/sdk/identity-verification.json', [
                'json' => $requestDTO->toArray(),
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'webapp_identity_verification_start');
                },
            ]);

            $decodedResponse = $this->decodeResponse($response)['data'] ?? [];

            if (!isset($decodedResponse['uuid']) || !isset($decodedResponse['url'])) {
                throw new IdentityVerificationServiceException(null, 'Service responded with an unexpected response body.');
            }

            return (new IdentityVerificationStartResponseDTO())
                ->setUuid($decodedResponse['uuid'])
                ->setUrl($decodedResponse['url']);
        } catch (TransferException $exception) {
            throw new IdentityVerificationServiceException($exception);
        }
    }
}
