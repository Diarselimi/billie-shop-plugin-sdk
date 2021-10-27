<?php

namespace App\Tests\Functional\Context;

use App\Tests\Helpers\TestUuidGenerator;
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
        $this->mockRequest(
            '/debtor.json',
            new ResponseStack(
                new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_register_debtor.json'))
            )
        );
    }

    /**
     * @Given /^I get from payments service a transaction "([^"]*)"$/
     */
    public function iGetFromPaymentsServiceTransactionForInvoiceResponse($transactionUuid)
    {
        $this->mockRequest(
            $i = sprintf('/transactions/%s.json', $transactionUuid),
            new ResponseStack(
                new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_get_ticket_transactions.json'), ['content-type' => 'application/json'], 200),
                new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_get_ticket_transactions.json'), ['content-type' => 'application/json'], 200)
            )
        );
    }

    /**
     * @Given /^I get from payments service get debtor response$/
     */
    public function iGetFromPaymentsServiceGetDebtorResponse()
    {
        $this->mockRequest(
            '/debtor/'.PaellaCoreContext::PAYMENT_DEBTOR_UUID.'.json',
            new ResponseStack(
                new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_get_debtor.json'), ['content-type' => 'application/json'], 200),
                new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_get_debtor.json'), ['content-type' => 'application/json'], 200)
            )
        );
    }

    /**
     * @Given /^I get from payments service get order details not found response$/
     */
    public function iGetFromPaymentsServiceGetOrderDetailsNotFoundResponse()
    {
        $this->mockRequest('/order/' . PaellaCoreContext::DUMMY_UUID4 . '.json', new MockResponse('', [], 404));
    }

    /**
     * @Given /^I get from payments service get order details response with first try fail$/
     */
    public function iGetFromPaymentsServiceGetOrderDetailsFirstTryFailResponse()
    {
        //This will test the retry mechanism.
        $this->mockRequest(
            '/order/' . PaellaCoreContext::DUMMY_UUID4 . '.json',
            new ResponseStack(
                new MockResponse('', [], 404),
                new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_order_details.json'))
            )
        );
    }

    /**
     * @Given /^I get from payments service get order details response$/
     */
    public function iGetFromPaymentsServiceGetOrderDetailsResponse()
    {
        $orderUuids = array_merge(
            [
                '123456a',
                '123456b',
                'test-order-uuid',
            ],
            TestUuidGenerator::getUuids()
        );

        foreach ($orderUuids as $orderUuid) {
            $this->mockRequest(
                '/order/' . $orderUuid . '.json',
                new MockResponse(
                    file_get_contents(__DIR__ . '/../resources/payments_service_order_details.json'),
                    ['Content-Type' => 'application/json']
                )
            );
        }
    }

    /**
     * @Given /^I get from payments service create ticket response$/
     */
    public function iGetFromPaymentsServiceCreateTicketResponse()
    {
        $this->mockRequest(
            '/order.json',
            new ResponseStack(
                new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_create_order.json'))
            )
        );
    }

    /**
     * @Given /^I get from payments service (a )?(modify|cancel) ticket response$/
     */
    public function iGetFromPaymentsServiceModifyTicketResponse()
    {
        $this->mockRequest(
            '/order.json',
            new ResponseStack(
                new MockResponse('')
            )
        );
    }

    /**
     * @Given /^I get from payments service two modify ticket responses$/
     */
    public function iGetFromPaymentsServiceTwoModifyTicketResponses()
    {
        $this->mockRequest(
            '/order.json',
            new ResponseStack(
                new MockResponse(''),
                new MockResponse('')
            )
        );
    }

    /**
     * @Given /^I get from payments service get orders details response$/
     */
    public function iGetFromPaymentsServiceGetOrdersDetailsResponse()
    {
        $this->mockRequest(
            '/orders.json',
            new ResponseStack(
                new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_orders_details.json'))
            )
        );
    }
}
