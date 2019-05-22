<?php

namespace App\Infrastructure\Alfred;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\DebtorCompanyFactory;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\DebtorCompany\IsEligibleForPayAfterDeliveryRequestDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class Alfred implements CompaniesServiceInterface, LoggingInterface
{
    use LoggingTrait;

    private $client;

    private $factory;

    public function __construct(Client $alfredClient, DebtorCompanyFactory $debtorFactory)
    {
        $this->client = $alfredClient;
        $this->factory = $debtorFactory;
    }

    public function getDebtor(int $debtorId): ?DebtorCompany
    {
        try {
            $response = $this->client->get("/debtor/$debtorId");
        } catch (TransferException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw new AlfredRequestException($exception->getCode(), $exception);
        }

        return $this->factory->createFromAlfredResponse($this->decodeResponse($response));
    }

    public function updateDebtor(int $debtorId, array $updateData): DebtorCompany
    {
        try {
            $response = $this->client->put("/debtor/$debtorId", [
                'json' => $updateData,
            ]);
        } catch (TransferException $exception) {
            throw new AlfredRequestException($exception->getCode(), $exception);
        }

        return $this->factory->createFromAlfredResponse($this->decodeResponse($response));
    }

    public function identifyDebtor(IdentifyDebtorRequestDTO $requestDTO): ?DebtorCompany
    {
        try {
            $response = $this->client->post("/debtor/identify", [
                'json' => $requestDTO->toArray(),
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'identify_debtor');
                },
            ]);
        } catch (ClientException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                $decodedResponse = $this->decodeResponse($exception->getResponse());

                if (isset($decodedResponse['suggestions']) && !empty($decodedResponse['suggestions'])) {
                    return $this->factory->createFromAlfredResponse(reset($decodedResponse['suggestions']), false);
                }

                return null;
            }

            throw new AlfredRequestException($exception->getCode(), $exception);
        } catch (TransferException $exception) {
            throw new AlfredRequestException($exception->getCode(), $exception);
        }

        $decodedResponse = $this->decodeResponse($response);

        return $this->factory->createFromAlfredResponse($decodedResponse);
    }

    public function lockDebtorLimit(string $debtorId, float $amount): void
    {
        try {
            $this->client->post("/debtor/$debtorId/lock", [
                'json' => [
                    'amount' => $amount,
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'debtor_lock_limit');
                },
            ]);
        } catch (TransferException $exception) {
            throw new AlfredRequestException($exception->getCode(), $exception);
        }
    }

    public function unlockDebtorLimit(string $debtorId, float $amount): void
    {
        try {
            $this->client->post("/debtor/$debtorId/unlock", [
                'json' => [
                    'amount' => $amount,
                ],
            ]);
        } catch (TransferException $exception) {
            throw new AlfredRequestException($exception->getCode(), $exception);
        }
    }

    public function isDebtorBlacklisted(string $debtorId): bool
    {
        try {
            $response = $this->client->get("/debtor/$debtorId/is-blacklisted");

            return $this->decodeResponse($response)['is_debtor_blacklisted'];
        } catch (TransferException $exception) {
            throw new AlfredRequestException($exception->getCode(), $exception);
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
            ]);

            return $this->decodeResponse($response)['is_eligible'];
        } catch (TransferException $exception) {
            throw new AlfredRequestException($exception->getCode(), $exception);
        }
    }

    public function markDuplicates(MerchantDebtorDuplicateDTO ...$duplicates): void
    {
        $payload = ['duplicates' => []];

        foreach ($duplicates as $duplicate) {
            /** @var MerchantDebtorDuplicateDTO $duplicate */
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
            throw new AlfredRequestException($exception->getCode(), $exception);
        }
    }

    private function decodeResponse(ResponseInterface $response): array
    {
        $decodedResponse = json_decode((string) $response->getBody(), true);

        if (!$decodedResponse) {
            throw new AlfredResponseDecodeException();
        }

        return $decodedResponse;
    }
}
