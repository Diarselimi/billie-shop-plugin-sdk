<?php

namespace App\Infrastructure\Salesforce;

use App\DomainModel\Salesforce\ClaimState;
use App\DomainModel\Salesforce\PauseDunningRequestBuilder;
use App\DomainModel\Salesforce\SalesforceInterface;
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

    private Client $client;

    public function __construct(Client $salesforceClient)
    {
        $this->client = $salesforceClient;
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

    public function getOrderClaimState(string $uuid): ClaimState
    {
        try {
            $response = $this->client->get("api/services/apexrest/v1/dci/$uuid");
            $decodedResponse = $this->decodeResponse($response);
            $this->validateDecodedResponseRoot($decodedResponse);

            return new ClaimState(
                $decodedResponse['result'][0]['dunning']['status'],
                $decodedResponse['result'][0]['dunning']['stage'],
                $decodedResponse['result'][0]['collection']['status'],
                $decodedResponse['result'][0]['collection']['stage'],
            );
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

    private function validateDecodedResponseRoot(array $decodedResponse): void
    {
        if (!isset($decodedResponse['result']) || !is_array($decodedResponse['result'])) {
            throw new ClientResponseDecodeException('Unexpected decoded response from Salesforce client.');
        }
    }
}
