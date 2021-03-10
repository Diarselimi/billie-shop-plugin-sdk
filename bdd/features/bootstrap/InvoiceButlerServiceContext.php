<?php

declare(strict_types=1);

namespace App\Tests\Functional\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;

class InvoiceButlerServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/butler/';
    }

    /**
     * @Given /^I get from invoice-butler service good response$/
     */
    public function invoiceButlerApiRespondedWithSuccess()
    {
        $this->mockRequest(
            '/invoices',
            new ResponseStack(
                new MockResponse(file_get_contents(__DIR__ . '/../resources/invoice_butler_good_response.json'))
            )
        );
    }

    /**
     * @Given invoice-butler call to :endpoint will respond with :statusCode and response:
     */
    public function invoiceButlerApiRespondedWith($endpoint, $statusCode, PyStringNode $response)
    {
        $this->mockRequestWith($endpoint, (string) $response, [], (int) $statusCode);
    }
}
