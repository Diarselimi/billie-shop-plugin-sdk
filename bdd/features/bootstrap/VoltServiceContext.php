<?php

declare(strict_types=1);

namespace App\Tests\Functional\Context;

use Behat\Behat\Context\Context;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;

class VoltServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/volt/';
    }

    /**
     * @Given /^I get from volt service good response$/
     */
    public function voltServiceRespondedWithSuccess()
    {
        $this->mockRequest(
            '/api/v1/fees/factoring/calculate',
            new ResponseStack(
                new MockResponse(file_get_contents(__DIR__ . '/../resources/volt_good_response.json'))
            )
        );
    }
}
