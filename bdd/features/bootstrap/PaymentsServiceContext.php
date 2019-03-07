<?php

use Behat\Behat\Context\Context;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;
use Symfony\Component\HttpKernel\KernelInterface;

class PaymentsServiceContext implements Context
{
    use MockServerTrait;

    public function __construct(KernelInterface $kernel)
    {
        $this->setServer($kernel);
    }

    /**
     * @Given /^I get from payments service register debtor positive response$/
     */
    public function iGetFromPaymentsServiceRegisterDebtorPositiveResponse()
    {
        $this->setMock('/debtor.json', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_register_debtor.json'))
        ));
    }

    /**
     * @Given /^I get from payments service get debtor response$/
     */
    public function iGetFromPaymentsServiceGetDebtorResponse()
    {
        $this->setMock('/debtor/test.json', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_get_debtor.json'))
        ));
    }

    /**
     * @Given /^I get from payments service create ticket response$/
     */
    public function iGetFromPaymentsServiceCreateTicketResponse()
    {
        $this->setMock('/order.json', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/payments_service_create_order.json'))
        ));
    }
}
