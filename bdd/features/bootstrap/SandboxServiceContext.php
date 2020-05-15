<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response as MockResponse;

class SandboxServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/paella_sandbox/';
    }

    /**
     * @Given I get from sandbox service a successful response on :endpoint call with body:
     */
    public function iGetFromSandboxServiceASuccessfulResponseOnCallWithBody($endpoint, PyStringNode $body)
    {
        $this->mockRequest($endpoint, new MockResponse($body->__toString()));
    }

    /**
     * @Given I get from sandbox service a response on :endpoint call with status code :status body:
     */
    public function iGetFromCompaniesServiceEndpointResponseWithStatusAndBody($endpoint, $status, PyStringNode $response)
    {
        $this->mockRequest($endpoint, new MockResponse($response->__toString(), [], $status));
    }
}
