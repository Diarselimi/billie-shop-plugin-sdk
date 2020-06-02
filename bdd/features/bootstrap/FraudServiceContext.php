<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use donatj\MockWebServer\ResponseStack;
use donatj\MockWebServer\Response as MockResponse;

class FraudServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/watson/';
    }

    /**
     * @Given /^Fraud API responded on the request with success$/
     */
    public function fraudApiResponsdedWithSuccess()
    {
        $this->mockRequest(
            '/find',
            new ResponseStack(
                new MockResponse(json_encode(['is_fraud' => false]))
            )
        );
    }
}
