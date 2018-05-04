<?php

namespace App\Infrastructure\Alfred;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Alfred\AlfredInterface;
use App\DomainModel\Alfred\DebtorDTO;
use App\DomainModel\Alfred\DebtorFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Symfony\Component\HttpFoundation\Response;

class Alfred implements AlfredInterface
{
    private $client;
    private $factory;

    public function __construct(Client $client, DebtorFactory $debtorFactory)
    {
        $this->client = $client;
        $this->factory = $debtorFactory;
    }

    public function getDebtor(int $debtorId): ?DebtorDTO
    {
        try {
            $response = $this->client->get("/debtor/$debtorId");
        } catch (TransferException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw new PaellaCoreCriticalException(
                'Alfred not available right now',
                PaellaCoreCriticalException::CODE_ALFRED_EXCEPTION,
                null,
                $exception
            );
        }

        $response = json_decode((string)$response->getBody(), true);
        if (!$response) {
            throw new PaellaCoreCriticalException(
                'Alfred response decode exception',
                PaellaCoreCriticalException::CODE_ALFRED_EXCEPTION
            );
        }

        return $this->factory->createFromAlfredResponse($response);
    }

    public function identifyDebtor(array $debtorData): ?DebtorDTO
    {
        try {
            $response = $this->client->post("/debtor/identify", [
                'json' => $debtorData,
            ]);
        } catch (TransferException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                return null;
            }

            throw new PaellaCoreCriticalException(
                'Alfred not available right now',
                PaellaCoreCriticalException::CODE_ALFRED_EXCEPTION,
                null,
                $exception
            );
        }

        $response = json_decode((string)$response->getBody(), true);
        if (!$response) {
            throw new PaellaCoreCriticalException(
                'Alfred response decode exception',
                PaellaCoreCriticalException::CODE_ALFRED_EXCEPTION
            );
        }

        return $this->factory->createFromAlfredResponse($response);
    }

    public function lockDebtorLimit(string $debtorId, float $amount): bool
    {
        try {
            $this->client->post("/debtor/$debtorId/lock", [
                'json' => [
                    'amount' => $amount,
                ]
            ]);
        } catch (TransferException $exception) {
            if ($exception->getCode() === Response::HTTP_PRECONDITION_FAILED) {
                return false;
            }

            throw new PaellaCoreCriticalException(
                'Alfred not available right now',
                PaellaCoreCriticalException::CODE_ALFRED_EXCEPTION,
                null,
                $exception
            );
        }

        return true;
    }

    public function unlockDebtorLimit(string $debtorId, float $amount): void
    {
        try {
            $this->client->post("/debtor/$debtorId/unlock", [
                'json' => [
                    'amount' => $amount,
                ]
            ]);
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                'Alfred not available right now',
                PaellaCoreCriticalException::CODE_ALFRED_EXCEPTION,
                null,
                $exception
            );
        }
    }
}
