<?php

use Behat\Behat\Context\Context;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;

class PaymentsServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/borscht/';
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
            new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_get_debtor.json')),
            new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_get_debtor.json'))
        ));
    }

    /**
     * @Given /^I get from payments service get order details not found response$/
     */
    public function iGetFromPaymentsServiceGetOrderDetailsNotFoundResponse()
    {
        $this->mockRequest('/order/' . PaellaCoreContext::DUMMY_UUID4 . '.json', new MockResponse('', [], 404));
    }

    /**
     * @Given /^I get from payments service get order details response$/
     */
    public function iGetFromPaymentsServiceGetOrderDetailsResponse()
    {
        $orderUuids = [
            PaellaCoreContext::DUMMY_UUID4,
            '123456a',
            '123456b',
        ];

        foreach ($orderUuids as $orderUuid) {
            $this->mockRequest('/order/' . $orderUuid . '.json', new MockResponse(
                file_get_contents(__DIR__ . '/../resources/payments_service_order_details.json')
            ));
        }
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

    /**
     * @Given /^I get from payments service modify ticket response$/
     */
    public function iGetFromPaymentsServiceModifyTicketResponse()
    {
        $this->mockRequest('/order.json', new ResponseStack(
            new MockResponse('')
        ));
    }

    /**
     * @Given /^I get from payments service two modify ticket responses$/
     */
    public function iGetFromPaymentsServiceTwoModifyTicketResponses()
    {
        $this->mockRequest('/order.json', new ResponseStack(
            new MockResponse(''),
            new MockResponse('')
        ));
    }

    /**
     * @Given /^I get from payments service get orders details response$/
     */
    public function iGetFromPaymentsServiceGetOrdersDetailsResponse()
    {
        $this->mockRequest('/orders.json', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_orders_details.json'))
        ));
    }
}
