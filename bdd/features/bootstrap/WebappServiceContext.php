<?php

declare(strict_types=1);

namespace App\Tests\Functional\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response as MockResponse;

class WebappServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/webapp/';
    }

    /**
     * @Given I get from webapp API an HTTP :status call to :method :endpoint with body:
     */
    public function iGetFromWebappAPIASuccessfulCallToPOSTWithBody($status, $method, $endpoint, PyStringNode $body)
    {
        $this->mockRequest($endpoint, new MockResponse($body->__toString(), [], (int) $status));
    }
}
