<?php

use Behat\Behat\Context\Context;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;

class SalesforceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/salesforce/';
    }

    /**
     * @Given /^Salesforce API responded for pause dunning request with success$/
     */
    public function salesforceAPIRespondedForPauseDunningRequestWithSuccess()
    {
        $this->mockRequest('/api/services/apexrest/v1/dunning', new ResponseStack(
            new MockResponse(json_encode(['success' => true, 'message' => null]))
        ));
    }

    /**
     * @Given Salesforce API responded for pause dunning request status code :statusCode and error message :errorMessage
     */
    public function salesforceAPIRespondedForPauseDunningRequestStatusCodeAndErrorMessage($statusCode, $errorMessage)
    {
        $this->mockRequest('/api/services/apexrest/v1/dunning', new ResponseStack(
            new MockResponse(json_encode(['success' => false, 'message' => $errorMessage]), [], (int) $statusCode)
        ));
    }

    /**
     * @Given I get from salesforce dunning status endpoint :status status
     */
    public function iGetFromSalesforceDunningStatusEndpointStatus($status)
    {
        $this->mockRequest('/api/services/apexrest/v1/dunning/test-order-uuid', new ResponseStack(
            new MockResponse(
                json_encode(
                    [
                        'success' => false,
                        'message' => null,
                        'result' => [
                            ['referenceUuid' => 'test-order-uuid', 'collectionClaimStatus' => $status],
                        ],
                    ]
                ),
                [],
                200
            )
        ));
    }
}
