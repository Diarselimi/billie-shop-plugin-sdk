<?php

namespace App\Infrastructure\Risky;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\RiskCheck\RiskCheckEntityFactory;
use App\DomainModel\RiskCheck\RiskCheckRepositoryInterface;
use App\DomainModel\Risky\RiskyInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class Risky implements RiskyInterface
{
    private $client;
    private $riskCheckRepository;
    private $riskCheckFactory;

    public function __construct(
        Client $client,
        RiskCheckRepositoryInterface $riskCheckRepository,
        RiskCheckEntityFactory $riskCheckFactory
    ) {
        $this->client = $client;
        $this->riskCheckRepository = $riskCheckRepository;
        $this->riskCheckFactory = $riskCheckFactory;
    }

    public function runCheck(OrderEntity $order, string $name): bool
    {
        try {
            $response = $this->client->post("/risk-check/order/$name", [
                'json' => [
                    'external_code' => $order->getExternalCode(),
                    'customer_id' => $order->getCustomerId(),
                ],
            ]);
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                'Risky not available right now',
                PaellaCoreCriticalException::CODE_RISKY_EXCEPTION,
                null,
                $exception
            );
        }

        $response = (string) $response->getBody();
        $response = json_decode($response, true);

        if (!$response) {
            throw new PaellaCoreCriticalException(
                "Risky response couldn't be decoded",
                PaellaCoreCriticalException::CODE_RISKY_EXCEPTION
            );
        }

        $check = $this->riskCheckFactory->create($order->getId(), $response['check_id'], $response['passed']);
        $this->riskCheckRepository->insert($check);

        return $check->isPassed();
    }
}
