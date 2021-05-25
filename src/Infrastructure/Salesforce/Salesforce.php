<?php

namespace App\Infrastructure\Salesforce;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\SalesforceInterface;
use App\DomainModel\Salesforce\PauseDunningRequestBuilder;
use App\Infrastructure\ClientResponseDecodeException;
use App\Infrastructure\DecodeResponseTrait;
use App\Infrastructure\Salesforce\Exception\SalesforceAuthenticationException;
use App\Infrastructure\Salesforce\Exception\SalesforceException;
use App\Infrastructure\Salesforce\Exception\SalesforceOpportunityNotFoundException;
use App\Infrastructure\Salesforce\Exception\SalesforcePauseDunningException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Response;

class Salesforce implements SalesforceInterface
{
    use DecodeResponseTrait;

    private $client;

    private OrderRepositoryInterface $orderRepository;

    public function __construct(Client $salesforceClient, OrderRepositoryInterface $orderRepository)
    {
        $this->client = $salesforceClient;
        $this->orderRepository = $orderRepository;
    }

    public function pauseDunning(PauseDunningRequestBuilder $requestBuilder): void
    {
        $exception = null;
        foreach ($requestBuilder->getRequests() as $request) {
            try {
                $this->client->post("api/services/apexrest/v1/dunning", [
                    'json' => $request,
                ]);

                return;
            } catch (RequestException $ex) {
                $exception = $ex;
            }
        }

        $this->handleException($exception);
    }

    public function getOrderDunningStatus(string $orderUuid): ?string
    {
        try {
            $response = $this->client->get("api/services/apexrest/v1/dunning/$orderUuid");

            $decodedResponse = $this->decodeResponse($response);

            $this->validateDecodedResponseRoot($decodedResponse);

            foreach ($decodedResponse['result'] as $result) {
                if ($orderUuid === $result['referenceUuid']) {
                    return $result['collectionClaimStatus'];
                }
            }

            return null;
        } catch (RequestException $exception) {
            $this->handleException($exception);
        }
    }

    public function getOrderCollectionsStatus(string $orderUuid): ?string
    {
        try {
            $response = $this->client->get("api/services/apexrest/v1/dci/$orderUuid");
            $decodedResponse = $this->decodeResponse($response);
            $this->validateDecodedResponseRoot($decodedResponse);

            return $decodedResponse['result'][0]['collection']['stage'] ?? null;
        } catch (RequestException $exception) {
            $this->handleException($exception);
        }
    }

    public function isDunningInProgress(Invoice $invoice): bool
    {
        $orders = $this->orderRepository->getByInvoice($invoice->getUuid());

        foreach ($orders as $order) {
            if ($this->getOrderCollectionsStatus($order->getUuid()) !== null) {
                return true;
            }
        }

        return false;
    }

    private function handleException(RequestException $exception): void
    {
        switch ($exception->getCode()) {
            case Response::HTTP_UNAUTHORIZED:
                throw new SalesforceAuthenticationException();
            case Response::HTTP_NOT_FOUND:
                throw new SalesforceOpportunityNotFoundException();
            case Response::HTTP_FORBIDDEN:
                $decodedResponse = $this->decodeResponse($exception->getResponse());

                throw new SalesforcePauseDunningException($decodedResponse['message']);
            default:
                throw new SalesforceException($exception->getMessage());
        }
    }

    private function validateDecodedResponseRoot(array $decodedResponse): void
    {
        if (!isset($decodedResponse['result']) || !is_array($decodedResponse['result'])) {
            throw new ClientResponseDecodeException('Unexpected decoded response from Salesforce client.');
        }
    }
}
