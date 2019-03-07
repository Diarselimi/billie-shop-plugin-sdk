<?php

namespace App\Infrastructure\Alfred;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\DebtorCompany\DebtorCompanyFactory;
use App\DomainModel\DebtorCompany\IdentifyDebtorRequestDTO;
use App\DomainModel\DebtorCompany\IsEligibleForPayAfterDeliveryRequestDTO;
use App\DomainModel\MerchantDebtor\MerchantDebtorDuplicateDTO;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class Alfred implements CompaniesServiceInterface
{
    private $client;

    private $factory;

    public function __construct(Client $client, DebtorCompanyFactory $debtorFactory)
    {
        $this->client = $client;
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
            ]);
        } catch (TransferException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw new AlfredRequestException($exception->getCode(), $exception);
        }

        return $this->factory->createFromAlfredResponse($this->decodeResponse($response));
    }

    public function identifyDebtorV2(IdentifyDebtorRequestDTO $requestDTO): ?DebtorCompany
    {
        try {
            $response = $this->client->post("/debtor/identify/v2", [
                'json' => $requestDTO->toArray(),
            ]);
        } catch (TransferException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw new AlfredRequestException($exception->getCode(), $exception);
        }

        $decodedResponse = $this->decodeResponse($response);

        return $this->factory->createFromAlfredResponse($decodedResponse);
    }

    public function lockDebtorLimit(string $debtorId, float $amount): bool
    {
        try {
            $this->client->post("/debtor/$debtorId/lock", [
                'json' => [
                    'amount' => $amount,
                ],
            ]);
        } catch (TransferException $exception) {
            if ($exception->getCode() === Response::HTTP_PRECONDITION_FAILED) {
                return false;
            }

            throw new AlfredRequestException($exception->getCode(), $exception);
        }

        return true;
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
            ]);

            return $this->decodeResponse($response)['is_eligible'];
        } catch (TransferException $exception) {
            throw new AlfredRequestException($exception->getCode(), $exception);
        }
    }

    public function markDuplicates(array $duplicates): void
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
