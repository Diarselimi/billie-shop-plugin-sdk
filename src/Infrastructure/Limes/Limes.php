<?php

declare(strict_types=1);

namespace App\Infrastructure\Limes;

use App\DomainModel\DebtorLimit\DebtorLimitDTO;
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

    private const DEFAULT_LIMIT_UPDATE_REASON = 'paella-update';

    private $client;

    private $factory;

    public function __construct(Client $limesClient, DebtorLimitFactory $factory)
    {
        $this->client = $limesClient;
        $this->factory = $factory;
    }

    public function check(string $debtorCompanyUuid, string $customerCompanyUuid, float $amount): bool
    {
        try {
            $response = $this->client->post("/debtor-limit/check", [
                'json' => [
                    'debtor_company_uuid' => $debtorCompanyUuid,
                    'customer_company_uuid' => $customerCompanyUuid,
                    'amount' => $amount,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'debtor_check_limit');
                },
            ]);

            $decodedResponse = $this->decodeResponse($response);

            return (bool) $decodedResponse['is_sufficient'];
        } catch (TransferException $exception) {
            throw new DebtorLimitServiceRequestException($exception);
        }
    }

    public function lock(string $debtorCompanyUuid, string $customerCompanyUuid, float $amount): void
    {
        try {
            $this->client->post("/debtor-limit/lock", [
                'json' => [
                    'debtor_company_uuid' => $debtorCompanyUuid,
                    'customer_company_uuid' => $customerCompanyUuid,
                    'amount' => $amount,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'debtor_lock_limit');
                },
            ]);
        } catch (TransferException $exception) {
            throw new DebtorLimitServiceRequestException($exception);
        }
    }

    public function release(string $debtorCompanyUuid, string $customerCompanyUuid, float $amount): void
    {
        try {
            $this->client->post("/debtor-limit/release", [
                'json' => [
                    'debtor_company_uuid' => $debtorCompanyUuid,
                    'customer_company_uuid' => $customerCompanyUuid,
                    'amount' => $amount,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'debtor_release_limit');
                },
            ]);
        } catch (TransferException $exception) {
            throw new DebtorLimitServiceRequestException($exception);
        }
    }

    public function retrieve(string $debtorCompanyUuid): DebtorLimitDTO
    {
        try {
            $response = $this->client->get("/debtor-limit/$debtorCompanyUuid");

            return $this->factory->createFromLimesResponse($this->decodeResponse($response));
        } catch (TransferException $exception) {
            throw new DebtorLimitServiceRequestException($exception);
        }
    }

    public function update(string $debtorCompanyUuid, string $customerCompanyUuid, float $newLimit): DebtorLimitDTO
    {
        try {
            $response = $this->client->post("/debtor-limit/update", [
                'json' => [
                    'debtor_company_uuid' => $debtorCompanyUuid,
                    'customer_company_uuid' => $customerCompanyUuid,
                    'financing_limit' => $newLimit,
                    'reason' => self::DEFAULT_LIMIT_UPDATE_REASON,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'debtor_update_limit');
                },
            ]);

            return $this->factory->createFromLimesResponse($this->decodeResponse($response));
        } catch (TransferException $exception) {
            throw new DebtorLimitServiceRequestException($exception);
        }
    }

    public function createDefaultDebtorCustomerLimit(string $customerCompanyUuid, float $defaultLimit): void
    {
        try {
            $this->client->put("/debtor-limit/default-customer-limit", [
                'json' => [
                    'customer_company_uuid' => $customerCompanyUuid,
                    'default_financing_limit' => $defaultLimit,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'create_debtor_customer_default_limit');
                },
            ]);
        } catch (TransferException $exception) {
            throw new DebtorLimitServiceRequestException($exception);
        }
    }
}
