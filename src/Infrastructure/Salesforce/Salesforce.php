<?php

namespace App\Infrastructure\Salesforce;

use App\DomainModel\Order\SalesforceInterface;
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

    public function __construct(Client $salesforceClient)
    {
        $this->client = $salesforceClient;
    }

    public function pauseOrderDunning(string $orderUuid, int $numberOfDays): void
    {
        try {
            $this->client->post("api/services/apexrest/v1/dunning", [
                'json' => [
                    'referenceUuid' => $orderUuid,
                    'numberOfDays' => $numberOfDays,
                ],
            ]);
        } catch (RequestException $exception) {
            $this->handleException($exception);
        }
    }

    public function getOrderDunningStatus(string $orderUuid): ? string
    {
        try {
            $response = $this->client->get("api/services/apexrest/v1/dunning/$orderUuid");

            $decodedResponse = $this->decodeResponse($response);

            if (!isset($decodedResponse['result']) || !is_array($decodedResponse['result'])) {
                throw new ClientResponseDecodeException('Unexpected decoded response from Salesforce client.');
            }

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
}
