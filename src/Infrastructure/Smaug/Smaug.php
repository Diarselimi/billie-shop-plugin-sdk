<?php

namespace App\Infrastructure\Smaug;

use App\DomainModel\MerchantUser\AuthenticationServiceCreateClientResponseDTO;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceAuthorizeTokenResponseDTO;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;

class Smaug implements AuthenticationServiceInterface, LoggingInterface
{
    use LoggingTrait;

    private $client;

    public function __construct(Client $smaugClient)
    {
        $this->client = $smaugClient;
    }

    public function authorizeToken(string $token): ? AuthenticationServiceAuthorizeTokenResponseDTO
    {
        try {
            $response = $this->client->get(
                '/oauth/authorization',
                [
                    'headers' => ['Authorization' => $token],
                    'on_stats' => function (TransferStats $stats) {
                        $this->logServiceRequestStats($stats, 'validate_oauth_token');
                    },
                ]
            );

            $decodedResponse = json_decode((string) $response->getBody(), true);

            return new AuthenticationServiceAuthorizeTokenResponseDTO(
                $decodedResponse['client_id'],
                $decodedResponse['user_id'] ?? null
            );
        } catch (TransferException $exception) {
            return null;
        }
    }

    public function createClient(string $clientName): AuthenticationServiceCreateClientResponseDTO
    {
        try {
            $response = $this->client->post(
                '/clients',
                [
                    'json' => ['name' => $clientName],
                    'on_stats' => function (TransferStats $stats) {
                        $this->logServiceRequestStats($stats, 'create_oauth_client');
                    },
                ]
            );

            $decodedResponse = json_decode((string) $response->getBody(), true);

            return new AuthenticationServiceCreateClientResponseDTO(
                $decodedResponse['client_id'],
                $decodedResponse['client_secret']
            );
        } catch (TransferException $exception) {
            $this->logSuppressedException($exception, 'Failed to create OAuth client', ['exception' => $exception]);

            throw new AuthenticationServiceException();
        }
    }
}
