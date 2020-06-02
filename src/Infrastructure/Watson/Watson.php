<?php

declare(strict_types=1);

namespace App\Infrastructure\Watson;

use App\DomainModel\Fraud\FraudRequestDTO;
use App\DomainModel\Fraud\FraudResponseDTO;
use App\DomainModel\Fraud\FraudServiceException;
use App\DomainModel\Fraud\FraudServiceInterface;
use App\Infrastructure\DecodeResponseTrait;
use App\Infrastructure\Watson\Factory\FraudResponseDTOFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;

class Watson implements FraudServiceInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private $client;

    private $factory;

    public function __construct(Client $watsonClient, FraudResponseDTOFactory $factory)
    {
        $this->client = $watsonClient;
        $this->factory = $factory;
    }

    public function check(FraudRequestDTO $request): FraudResponseDTO
    {
        try {
            $response = $this->client->post('check-fraud', [
                'json' => $request->toArray(),
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'check_fraud');
                },
            ]);

            return $this->factory->createFromJson(
                $this->decodeResponse($response)
            );
        } catch (ClientException | TransferException $exception) {
            throw new FraudServiceException($exception);
        }
    }
}
