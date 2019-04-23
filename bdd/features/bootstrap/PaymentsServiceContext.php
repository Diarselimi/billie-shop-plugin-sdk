<?php

use Behat\Behat\Context\Context;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;

class PaymentsServiceContext implements Context
{
    private const MOCK_SERVER_PORT = 8022;

    use MockServerTrait;

    public function __construct()
    {
        $this->startServer(self::MOCK_SERVER_PORT);
    }

    /**
     * @AfterScenario
     */
    public function afterScenario()
    {
        $this->stopServer();
    }

    /**
     * @Given /^I get from payments service register debtor positive response$/
     */
    public function iGetFromPaymentsServiceRegisterDebtorPositiveResponse()
    {
        $this->mockRequest('/debtor.json', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_register_debtor.json'))
        ));
    }

    /**
     * @Given /^I get from payments service get debtor response$/
     */
    public function iGetFromPaymentsServiceGetDebtorResponse()
    {
        $this->mockRequest('/debtor/test.json', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_get_debtor.json'))
        ));
    }

    /**
     * @Given /^I get from payments service get order details response$/
     */
    public function iGetFromPaymentsServiceGetOrderDetailsResponse()
    {
        $this->mockRequest('/order/1.json', new MockResponse(
            file_get_contents(__DIR__ . '/../resources/payments_service_order_details.json')
        ));
    }

    /**
     * @Given /^I get from payments service create ticket response$/
     */
    public function iGetFromPaymentsServiceCreateTicketResponse()
    {
        $this->mockRequest('/order.json', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_create_order.json'))
        ));
    }
}
