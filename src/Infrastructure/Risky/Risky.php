<?php

namespace App\Infrastructure\Risky;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\RiskCheck\RiskCheckEntityFactory;
use App\DomainModel\RiskCheck\RiskCheckRepositoryInterface;
use App\DomainModel\Risky\RiskyInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TransferException;

class Risky implements RiskyInterface, LoggingInterface
{
    use LoggingTrait;

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

    public function runOrderCheck(OrderEntity $order, string $name): bool
    {
        try {
            $httpResponse = $this->client->post("/risk-check/order/$name", [
                'json' => [
                    'external_code' => $order->getExternalCode(),
                    'merchant_id' => $order->getMerchantId(),
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

        $response = (string)$httpResponse->getBody();
        $response = json_decode($response, true);

        if (!$response) {
            $this->logError("Risky response couldn't be decoded", [
                'response' => (string) $httpResponse->getBody(),
            ]);

            throw new PaellaCoreCriticalException(
                "Risky response couldn't be decoded",
                PaellaCoreCriticalException::CODE_RISKY_EXCEPTION
            );
        }

        $this->saveCheckResult($order->getId(), $response['check_id'], $name, $response['passed']);

        return $response['passed'];
    }

    public function runDebtorScoreCheck(OrderContainer $orderContainer, ?string $crefoId): bool
    {
        $debtorData = $orderContainer->getDebtorExternalData();
        $address = $orderContainer->getDebtorExternalDataAddress();
        $id = $orderContainer->getOrder()->getId();

        try {
            $httpResponse = $this->client->post("/risk-check/company/company_b2b_score", [
                'json' => [
                    'company_name' => $debtorData->getName(),
                    'house' => $address->getHouseNumber(),
                    'street' => $address->getStreet(),
                    'postal_code' => $address->getPostalCode(),
                    'city' => $address->getCity(),
                    'country' => $address->getCountry(),
                    'registration_number' => $debtorData->getRegistrationNumber(),
                    'registration_court' => $debtorData->getRegistrationCourt(),
                    'tax_id' => $debtorData->getTaxId(),
                    'tax_number' => $debtorData->getTaxNumber(),
                    'crefo_id' => $crefoId,
                ],
            ]);
        } catch (ClientException $exception) {
            $this->logError("Risky couldn't score debtor", [
                'code' => $exception->getCode(),
                'error' => $exception->getMessage(),
            ]);

            $this->saveCheckResult($id, null, 'company_b2b_score', false);

            return false;
        } catch (TransferException $exception) {
            throw new PaellaCoreCriticalException(
                "Risky returned exception on debtor score check",
                PaellaCoreCriticalException::CODE_RISKY_EXCEPTION
            );
        }

        $response = (string) $httpResponse->getBody();
        $response = json_decode($response, true);

        if (!$response) {
            $this->logError("Risky response couldn't be decoded", [
                'response' => (string) $httpResponse->getBody(),
            ]);

            throw new PaellaCoreCriticalException(
                "Risky response couldn't be decoded",
                PaellaCoreCriticalException::CODE_RISKY_EXCEPTION
            );
        }

        $this->saveCheckResult($id, $response['check_id'], 'company_b2b_score', $response['passed']);

        return $response['passed'];
    }

    private function saveCheckResult(int $orderId, ?int $checkId, string $name, bool $isPassed): void
    {
        $check = $this->riskCheckFactory->create($orderId, $checkId, $name, $isPassed);
        $this->riskCheckRepository->insert($check);
    }
}
