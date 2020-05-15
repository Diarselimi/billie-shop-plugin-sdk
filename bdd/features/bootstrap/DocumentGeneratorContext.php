<?php

use Behat\Behat\Context\Context;
use donatj\MockWebServer\ResponseStack;
use donatj\MockWebServer\Response as MockResponse;

class DocumentGeneratorContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/document_generator/';
    }

    /**
     * @Given /^I successfully create sepa B2B document$/
     */
    public function iSuccessfullyCreateSepaB2BDocument()
    {
        $this->mockRequest('/generate/b2b_mandate', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/pdf_generator_pdf_base_64.txt'), [], 200)
        ));
    }
}
