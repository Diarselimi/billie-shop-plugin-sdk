<?php

use Behat\Behat\Context\Context;
use donatj\MockWebServer\ResponseStack;
use donatj\MockWebServer\Response as MockResponse;

class NachosContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/nachos/';
    }

    /**
     * @Given /^I get from files service a good response$/
     */
    public function iGetFromFilesServiceAGoodResponse()
    {
        $this->mockRequest('/files', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/file_service_good_response.json'), [], 200)
        ));
    }

    /**
     * @Given /^I get from files service existing file content$/
     */
    public function iGetFromFilesServiceExistingFileContent()
    {
        $this->mockRequest('/files/c7be46c0-e049-4312-b274-258ec5aeeb70/raw', new ResponseStack(
            new MockResponse(
                "dummy_string_as_content_for_pdf_file",
                [
                    'Content-Type' => 'application/force-download',
                    'Content-Transfer-Encoding' => 'binary',
                    'Content-Disposition' => 'attachment; filename="sepa-mandate.pdf"',
                ],
                200
            )
        ));
    }
}
