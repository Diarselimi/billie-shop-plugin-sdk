<?php

declare(strict_types=1);

namespace App\Tests\Functional\Context;

use Behat\Behat\Context\Context;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;

class FraudServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/watson/';
    }

    /**
     * @Given /^I get from Fraud service a non fraud response$/
     */
    public function fraudApiRespondedWithSuccess()
    {
        $this->mockRequest(
            '/check-invoice-fraud',
            new ResponseStack(
                new MockResponse(json_encode(['is_fraud' => false]))
            )
        );
    }

    /**
     * @Given /^I get from Fraud service a fraud response$/
     */
    public function fraudApiResponsdedWithFailure()
    {
        $this->mockRequest(
            '/check-invoice-fraud',
            new ResponseStack(
                new MockResponse(json_encode(['is_fraud' => true]))
            )
        );
    }

    /**
     * @Given /^I get from Fraud service a non fraud iban response$/
     */
    public function fraudApiRespondedWithIbanNotFraud()
    {
        $this->mockRequest(
            '/check-iban-fraud',
            new ResponseStack(
                new MockResponse(
                    json_encode(['is_fraud' => false]),
                    ['Content-Type' => 'application/json']
                )
            )
        );
    }

    /**
     * @Given /^I get from Fraud service a fraud iban response$/
     */
    public function fraudApiRespondedWithIbanFraud()
    {
        $this->mockRequest(
            '/check-iban-fraud',
            new ResponseStack(
                new MockResponse(
                    json_encode(['is_fraud' => true]),
                    ['Content-Type' => 'application/json']
                )
            )
        );
    }
}
