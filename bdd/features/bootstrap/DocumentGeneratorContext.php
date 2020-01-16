<?php

use Behat\Behat\Context\Context;
use donatj\MockWebServer\ResponseStack;
use donatj\MockWebServer\Response as MockResponse;

class DocumentGeneratorContext implements Context
{
    private const MOCK_SERVER_PORT = 8030;

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
     * @Given /^I successfully create sepa B2B document$/
     */
    public function iSuccessfullyCreateSepaB2BDocument()
    {
        $this->mockRequest('/generate/b2b_mandate', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/pdf_generator_pdf_base_64.txt'), [], 200)
        ));
    }
}
