<?php

namespace App\Infrastructure\Smaug;

use App\DomainModel\MerchantUser\AuthenticationServiceAuthorizeTokenResponseDTO;
use App\DomainModel\MerchantUser\AuthenticationServiceConflictRequestException;
use App\DomainModel\MerchantUser\AuthenticationServiceCreateClientResponseDTO;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;
use App\DomainModel\MerchantUser\AuthenticationServiceTokenResponseDTO;
use App\DomainModel\MerchantUser\AuthenticationServiceUserResponseDTO;
use App\DomainModel\MerchantUser\GetMerchantCredentialsDTO;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;

class Smaug implements AuthenticationServiceInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private $client;

    private $smaugClientId;

    private $smaugClientSecret;

    public function __construct(Client $smaugClient, string $smaugClientId, string $smaugClientSecret)
    {
        $this->client = $smaugClient;
        $this->smaugClientId = $smaugClientId;
        $this->smaugClientSecret = $smaugClientSecret;
    }

    public function authorizeToken(string $token): ?AuthenticationServiceAuthorizeTokenResponseDTO
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

            $decodedResponse = $this->decodeResponse($response);

            return new AuthenticationServiceAuthorizeTokenResponseDTO(
                $decodedResponse['client_id'],
                $decodedResponse['user_id'] ?? null,
                $decodedResponse['email']
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

            $decodedResponse = $this->decodeResponse($response);

            return new AuthenticationServiceCreateClientResponseDTO(
                $decodedResponse['client_id'],
                $decodedResponse['client_secret']
            );
        } catch (TransferException $exception) {
            $this->logSuppressedException($exception, 'Failed to create OAuth client', ['exception' => $exception]);
            if ($this->isTransferExceptionStatusCode($exception, 409)) {
                throw new AuthenticationServiceConflictRequestException($exception);
            }

            throw new AuthenticationServiceRequestException($exception);
        }
    }

    public function createUser(string $email, string $password): AuthenticationServiceUserResponseDTO
    {
        try {
            $response = $this->client->post(
                '/users',
                [
                    'json' => ['email' => $email, 'password' => $password],
                    'on_stats' => function (TransferStats $stats) {
                        $this->logServiceRequestStats($stats, 'create_oauth_user');
                    },
                ]
            );

            $decodedResponse = $this->decodeResponse($response);

            return new AuthenticationServiceUserResponseDTO(
                $decodedResponse['user_id'],
                $decodedResponse['user_email']
            );
        } catch (TransferException $exception) {
            $this->logSuppressedException($exception, 'Failed to create OAuth user', ['exception' => $exception]);
            if ($this->isTransferExceptionStatusCode($exception, 409)) {
                throw new AuthenticationServiceConflictRequestException($exception);
            }

            throw new AuthenticationServiceRequestException($exception);
        }
    }

    public function requestUserToken(string $email, string $password): AuthenticationServiceTokenResponseDTO
    {
        try {
            $response = $this->client->post(
                '/oauth/token',
                [
                    'json' => [
                        'grant_type' => 'password',
                        'client_id' => $this->smaugClientId,
                        'client_secret' => $this->smaugClientSecret,
                        'username' => $email,
                        'password' => $password,
                        'scope' => 'all',
                    ],
                    'on_stats' => function (TransferStats $stats) {
                        $this->logServiceRequestStats($stats, 'obtain_user_token');
                    },
                ]
            );

            $decodedResponse = $this->decodeResponse($response);

            return new AuthenticationServiceTokenResponseDTO(
                $decodedResponse['token_type'],
                $decodedResponse['expires_in'],
                $decodedResponse['access_token'],
                $decodedResponse['refresh_token']
            );
        } catch (TransferException $exception) {
            $this->logSuppressedException($exception, 'Failed to obtain user token', ['exception' => $exception]);

            throw new AuthenticationServiceRequestException($exception);
        }
    }

    public function revokeToken(string $token): void
    {
        try {
            $this->client->post(
                '/oauth/token/revoke',
                [
                    'headers' => [
                        'Authorization' => $token,
                    ],
                    'on_stats' => function (TransferStats $stats) {
                        $this->logServiceRequestStats($stats, 'revoke_token');
                    },
                ]
            );
        } catch (TransferException $exception) {
            $this->logSuppressedException($exception, 'Failed to revoke token', ['exception' => $exception]);

            throw new AuthenticationServiceRequestException($exception);
        }
    }

    private function isTransferExceptionStatusCode(\Throwable $exception, int $statusCode): bool
    {
        return ($exception instanceof RequestException)
            && $exception->getResponse()
            && $exception->getResponse()->getStatusCode() === $statusCode;
    }

    public function getCredentials(string $clientId): ?GetMerchantCredentialsDTO
    {
        try {
            $response = $this->client->get("/client/{$clientId}/credentials", [
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'get_client_credentials');
                },
            ]);

            $decodedResponse = $this->decodeResponse($response);

            $clientId = $decodedResponse['client_id'] ?? null;
            $secret = $decodedResponse['secret'] ?? null;

            return $clientId ? (new GetMerchantCredentialsDTO($clientId, $secret)) : null;
        } catch (TransferException $exception) {
            throw new AuthenticationServiceRequestException($exception);
        }
    }

    public function getUsersByUuids(array $uuids): array
    {
        if (empty($uuids)) {
            throw new AuthenticationServiceRequestException(null, 'User UUIDs cannot be empty');
        }

        try {
            $response = $this->client->request(
                'get',
                '/users',
                [
                    'query' => ['uuids' => $uuids],
                    'on_stats' => function (TransferStats $stats) {
                        $this->logServiceRequestStats($stats, 'get_oauth_users');
                    },
                ]
            );

            $decodedResponse = $this->decodeResponse($response);

            $dtos = [];
            foreach ($decodedResponse as $userData) {
                $dtos[$userData['user_id']] = new AuthenticationServiceUserResponseDTO(
                    $userData['user_id'],
                    $userData['user_email']
                );
            }

            return $dtos;
        } catch (TransferException $exception) {
            $this->logSuppressedException($exception, 'Failed to get OAuth users', ['exception' => $exception]);

            throw new AuthenticationServiceRequestException($exception);
        }
    }
}
