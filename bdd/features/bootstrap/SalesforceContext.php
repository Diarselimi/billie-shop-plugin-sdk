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
        $this->startServer(self::MOCK_SERVER_PORT);

        register_shutdown_function(function () {
            $this->stopServer();
        });
    }

    /**
     * @AfterScenario
     */
    public function afterScenario()
    {
        $this->stopServer();
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
}
