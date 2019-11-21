<?php

declare(strict_types=1);

namespace App\Infrastructure\Limes;

use App\DomainModel\DebtorLimit\DebtorLimit;
use App\DomainModel\DebtorLimit\DebtorLimitFactory;
use App\DomainModel\DebtorLimit\DebtorLimitServiceInterface;
use App\DomainModel\DebtorLimit\DebtorLimitServiceRequestException;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;

class Limes implements DebtorLimitServiceInterface, LoggingInterface
{
    use DecodeResponseTrait, LoggingTrait;

    private $client;

    private $factory;

    public function __construct(Client $limesClient, DebtorLimitFactory $factory)
    {
        $this->client = $limesClient;
        $this->factory = $factory;
    }

    public function check(string $debtorCompanyUuid, float $amount): bool
    {
        try {
            $response = $this->client->post("/debtor-limit/check", [
                'json' => [
                    'debtor_company_uuid' => $debtorCompanyUuid,
                    'amount' => $amount,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'debtor_check_limit');
                },
            ]);

            $decodedResponse = $this->decodeResponse($response);

            return (bool) $decodedResponse['is_sufficient'];
        } catch (TransferException $exception) {
            throw new DebtorLimitServiceRequestException();
        }
    }

    public function lock(string $debtorCompanyUuid, float $amount): void
    {
        try {
            $this->client->post("/debtor-limit/lock", [
                'json' => [
                    'debtor_company_uuid' => $debtorCompanyUuid,
                    'amount' => $amount,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'debtor_lock_limit');
                },
            ]);
        } catch (TransferException $exception) {
            throw new DebtorLimitServiceRequestException();
        }
    }

    public function release(string $debtorCompanyUuid, float $amount): void
    {
        try {
            $this->client->post("/debtor-limit/release", [
                'json' => [
                    'debtor_company_uuid' => $debtorCompanyUuid,
                    'amount' => $amount,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'debtor_release_limit');
                },
            ]);
        } catch (TransferException $exception) {
            throw new DebtorLimitServiceRequestException();
        }
    }

    public function retrieve(string $debtorCompanyUuid): DebtorLimit
    {
        try {
            $response = $this->client->get("/debtor-limit/$debtorCompanyUuid");

            return $this->factory->createFromLimesResponse($this->decodeResponse($response));
        } catch (TransferException $exception) {
            throw new DebtorLimitServiceRequestException();
        }
    }
}
