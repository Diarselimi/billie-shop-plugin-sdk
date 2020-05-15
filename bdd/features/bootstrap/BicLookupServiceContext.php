<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response as MockResponse;

class BicLookupServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/fintech_toolbox/';
    }

    /**
     * @Given I get from BIC lookup service call to :endpoint a response with status :status and body:
     */
    public function iGetFromBICLookupServiceCallToAResponseWithStatusAndBody($endpoint, $status, PyStringNode $body)
    {
        $this->mockRequest($endpoint, new MockResponse($body->__toString(), [], (int) $status));
    }
}
