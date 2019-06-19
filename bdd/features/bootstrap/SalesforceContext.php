<?php

use Behat\Behat\Context\Context;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;

class SalesforceContext implements Context
{
    private const MOCK_SERVER_PORT = 8024;

    use MockServerTrait;

    public function __construct()
    {
        register_shutdown_function(function () {
            self::stopServer();
        });
    }

    /**
     * @BeforeSuite
     */
    public static function beforeSuite()
    {
        self::startServer(self::MOCK_SERVER_PORT);
    }

    /**
     * @AfterSuite
     */
    public static function afterSuite()
    {
        self::stopServer();
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
        $this->mockRequest('/api/services/apexrest/v1/dunning/test123', new ResponseStack(
            new MockResponse(
                json_encode(
                    [
                        'success' => false,
                        'message' => null,
                        'result' => [
                            ['referenceUuid' => 'test123', 'collectionClaimStatus' => $status],
                        ],
                    ]
                ),
                [],
                200
            )
        ));
    }
}
