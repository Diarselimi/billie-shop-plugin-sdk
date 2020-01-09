<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response as MockResponse;

class WebappServiceContext implements Context
{
    private const MOCK_SERVER_PORT = 8028;

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
     * @Given I get from webapp API an HTTP :status call to :method :endpoint with body:
     */
    public function iGetFromWebappAPIASuccessfulCallToPOSTWithBody($status, $method, $endpoint, PyStringNode $body)
    {
        $this->mockRequest($endpoint, new MockResponse($body->__toString(), [], (int) $status));
    }
}
