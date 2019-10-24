<?php

namespace App\Infrastructure\Alfred;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\DebtorCompanyFactory;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\DebtorCompany\IsEligibleForPayAfterDeliveryRequestDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;
use App\Infrastructure\ClientResponseDecodeException;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;
use Symfony\Component\HttpFoundation\Response;

class Alfred implements CompaniesServiceInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private const IDENTIFICATION_REQUEST_TIMEOUT = 15;

    private const SCORING_REQUEST_TIMEOUT = 15;

    private $client;

    private $factory;

    public function __construct(Client $alfredClient, DebtorCompanyFactory $debtorFactory)
    {
        $this->client = $alfredClient;
        $this->factory = $debtorFactory;
    }

    public function getDebtor(int $debtorCompanyId): ?DebtorCompany
    {
        return $this->doGetDebtor($debtorCompanyId);
    }

    /**
     * @param  array           $debtorIds
     * @return DebtorCompany[]
     */
    public function getDebtors(array $debtorIds): array
    {
        try {
            $response = $this->client->get("/debtors", [
                'query' => [
                    'ids' => $debtorIds,
                ],
            ]);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException();
        }

        return $this->factory->createFromMultipleDebtorCompaniesResponse($this->decodeResponse($response));
    }

    public function getDebtorByUuid(string $debtorCompanyUuid): ?DebtorCompany
    {
        return $this->doGetDebtor($debtorCompanyUuid);
    }

    private function doGetDebtor($identifier): ?DebtorCompany
    {
        try {
            $response = $this->client->get("/debtor/{$identifier}");

            return $this->factory->createFromAlfredResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function updateDebtor(int $debtorId, array $updateData): DebtorCompany
    {
        try {
            $response = $this->client->put("/debtor/$debtorId", [
                'json' => $updateData,
            ]);

            return $this->factory->createFromAlfredResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function synchronizeDebtor(int $debtorId): DebtorCompany
    {
        try {
            $response = $this->client->post(
                "/debtor/$debtorId/synchronize"
            );

            return $this->factory->createFromAlfredResponse($this->decodeResponse($response));
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function identifyDebtor(IdentifyDebtorRequestDTO $requestDTO): ?DebtorCompany
    {
        try {
            $response = $this->client->post("/debtor/identify", [
                'json' => $requestDTO->toArray(),
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'identify_debtor');
                },
                'timeout' => self::IDENTIFICATION_REQUEST_TIMEOUT,
            ]);

            $decodedResponse = $this->decodeResponse($response);

            return $this->factory->createFromAlfredResponse($decodedResponse);
        } catch (ClientException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                $decodedResponse = $this->decodeResponse($exception->getResponse());

                if (isset($decodedResponse['suggestions']) && !empty($decodedResponse['suggestions'])) {
                    return $this->factory->createFromAlfredResponse(reset($decodedResponse['suggestions']), false);
                }

                return null;
            }

            throw new CompaniesServiceRequestException($exception);
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function lockDebtorLimit(string $debtorUuid, float $amount): void
    {
        try {
            $this->client->post("/debtor/$debtorUuid/lock", [
                'json' => [
                    'amount' => $amount,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'debtor_lock_limit');
                },
            ]);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function unlockDebtorLimit(string $debtorUuid, float $amount): void
    {
        try {
            $this->client->post("/debtor/$debtorUuid/unlock", [
                'json' => [
                    'amount' => $amount,
                ],
            ]);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function isEligibleForPayAfterDelivery(IsEligibleForPayAfterDeliveryRequestDTO $requestDTO): bool
    {
        try {
            $response = $this->client->get("/debtor/{$requestDTO->getDebtorId()}/is-eligible-for-pay-after-delivery", [
                'query' => [
                    'is_sole_trader' => $requestDTO->isSoleTrader(),
                    'has_paid_invoice' => $requestDTO->isHasPaidInvoice(),
                    'crefo_low_score_threshold' => $requestDTO->getCrefoLowScoreThreshold(),
                    'crefo_high_score_threshold' => $requestDTO->getCrefoHighScoreThreshold(),
                    'schufa_low_score_threshold' => $requestDTO->getSchufaLowScoreThreshold(),
                    'schufa_average_score_threshold' => $requestDTO->getSchufaAverageScoreThreshold(),
                    'schufa_high_score_threshold' => $requestDTO->getSchufaHighScoreThreshold(),
                    'schufa_sole_trader_score_threshold' => $requestDTO->getSchufaSoleTraderScoreThreshold(),
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'score_debtor');
                },
                'timeout' => self::SCORING_REQUEST_TIMEOUT,
            ]);

            return $this->decodeResponse($response)['is_eligible'];
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }

    public function markDuplicates(MerchantDebtorDuplicateDTO ...$duplicates): void
    {
        $payload = ['duplicates' => []];

        foreach ($duplicates as $duplicate) {
            $payload['duplicates'][] = [
                'debtor_id' => $duplicate->getDebtorId(),
                'is_duplicate_of' => $duplicate->getParentDebtorId(),
            ];
        }

        try {
            $this->client->post("/debtor/mark-duplicates", [
                'json' => $payload,
            ]);
        } catch (TransferException $exception) {
            throw new CompaniesServiceRequestException($exception);
        }
    }
}
