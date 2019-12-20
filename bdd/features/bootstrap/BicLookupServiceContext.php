<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response as MockResponse;

class BicLookupServiceContext implements Context
{
    private const MOCK_SERVER_PORT = 8027;

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
     * @Given I get from BIC lookup service call to :endpoint a response with status :status and body:
     */
    public function iGetFromBICLookupServiceCallToAResponseWithStatusAndBody($endpoint, $status, PyStringNode $body)
    {
        $this->mockRequest($endpoint, new MockResponse($body->__toString(), [], (int) $status));
    }
}
