<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response as MockResponse;

class SandboxServiceContext implements Context
{
    private const MOCK_SERVER_PORT = 8026;

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
     * @Given I get from sandbox service a successful response on :endpoint call with body:
     */
    public function iGetFromSandboxServiceASuccessfulResponseOnCallWithBody($endpoint, PyStringNode $body)
    {
        $this->mockRequest($endpoint, new MockResponse($body->__toString()));
    }

    /**
     * @Given I get from sandbox service a response on :endpoint call with status code :status body:
     */
    public function iGetFromCompaniesServiceEndpointResponseWithStatusAndBody($endpoint, $status, PyStringNode $response)
    {
        $this->mockRequest($endpoint, new MockResponse($response->__toString(), [], $status));
    }
}
