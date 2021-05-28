<?php

declare(strict_types=1);

namespace App\Tests\Functional\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use DateInterval;
use DateTime;
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
     * @Given /^I get from invoice-butler service good response no CreditNotes$/
     */
    public function invoiceButlerNoCNApiRespondedWithSuccess()
    {
        $this->mockRequest(
            '/invoices',
            new ResponseStack(
                new MockResponse(file_get_contents(__DIR__ . '/../resources/invoice_butler_good_response_no_CN.json'))
            )
        );
    }

    /**
     * @Given /^I get from invoice-butler service empty response$/
     */
    public function invoiceButlerEmptyResponseApiRespondedWithSuccess()
    {
        $this->mockRequest(
            '/invoices',
            new ResponseStack(
                new MockResponse('')
            )
        );
    }

    /**
     * @Given /^I get from invoice-butler service no invoices response$/
     */
    public function invoiceButlerApiRespondedWithNoInvoices()
    {
        $this->mockRequest('/invoices', new MockResponse('[]'));
    }

    /**
     * @Given /^I get from invoice-butler service no invoices and later one invoice responses$/
     */
    public function invoiceButlerApiRespondedWithNoInvoicesAndOneInvoiceLater()
    {
        $this->mockRequest('/invoices', new ResponseStack(
            new MockResponse('[]'),
            new MockResponse(file_get_contents(__DIR__ . '/../resources/invoice_butler_good_response.json'))
        ));
    }

    /**
     * @Given /^I get from invoice-butler service no invoices and later one invoice no cns responses$/
     */
    public function invoiceButlerApiRespondedWithNoInvoicesAndOneInvoiceNoCNsLater()
    {
        $this->mockRequest('/invoices', new ResponseStack(
            new MockResponse('[]'),
            new MockResponse(file_get_contents(__DIR__ . '/../resources/invoice_butler_good_response_no_CN.json'))
        ));
    }

    /**
     * @Given /^I get from invoice-butler service an invoice that can be extended$/
     */
    public function invoiceButlerApiRespondedWithExtendableInvoice()
    {
        $invoiceResponseMock = file_get_contents(__DIR__ . '/../resources/invoice_butler_good_response.json');
        $invoiceResponseMock = $this->changeDueDateToNextWeek($invoiceResponseMock);
        $this->mockRequest(
            '/invoices',
            new ResponseStack(
                new MockResponse($invoiceResponseMock)
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

    /**
     * @param $invoiceResponse
     * @return false|string
     */
    private function changeDueDateToNextWeek($invoiceResponse)
    {
        $invoiceData = json_decode($invoiceResponse, true);
        $nextWeek = (new DateTime())->add(new DateInterval('P7D'));
        $invoiceData[0]['due_date'] = $nextWeek->format('Y-m-d H:i:s');
        $invoiceResponse = json_encode(array_values($invoiceData));

        return $invoiceResponse;
    }
}
