<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;

class CompaniesServiceContext implements Context
{
    private const MOCK_SERVER_PORT = 8021;

    use MockServerTrait;

    public function __construct()
    {
        register_shutdown_function(function () {
            self::stopServer();
        });
    }

    /**
     * @BeforeSuite
     */
    public static function beforeSuite()
    {
        self::startServer(self::MOCK_SERVER_PORT);
    }

    /**
     * @AfterSuite
     */
    public static function afterSuite()
    {
        self::stopServer();
    }

    /**
     * @Given /^I get from companies service identify no match response$/
     */
    public function iGetFromCompaniesServiceIdentifyNoMatchResponse()
    {
        $this->mockRequest('/debtor/identify', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_no_match.json'), [], 404)
        ));
    }

    /**
     * @Given /^I get from companies service identify match response$/
     */
    public function iGetFromCompaniesServiceIdentifyMatchResponse()
    {
        $this->mockRequest('/debtor/identify', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match_trusted_source.json'))
        ));
    }

    /**
     * @Given /^I get from companies service a good debtor strict match response$/
     */
    public function iGetFromCompaniesServiceGoodStrictMatchResponse()
    {
        $this->mockRequest('/debtor/strict-match', new ResponseStack(
            new MockResponse('', [], 202)
        ));
    }

    /**
     * @Given /^I get from companies service a bad debtor strict match response$/
     */
    public function iGetFromCompaniesServiceBadStrictMatchResponse()
    {
        $this->mockRequest('/debtor/strict-match', new ResponseStack(
            new MockResponse('', [], 400)
        ));
    }

    /**
     * @Given /^I get from companies service get debtor response$/
     */
    public function iGetFromCompaniesServiceGetDebtorResponse()
    {
        $this->mockRequest('/debtor/1', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match_trusted_source.json')),
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match_trusted_source.json'))
        ));

        $this->mockRequest('/debtor/10', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match_trusted_source.json'))
        ));

        $this->mockRequest('/debtor/' . PaellaCoreContext::DEBTOR_COMPANY_UUID, new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match_trusted_source.json'))
        ));
    }

    /**
     * @Given /^I get from companies service update debtor positive response$/
     */
    public function iGetFromCompaniesServiceUpdateDebtorPositiveResponse()
    {
        $this->mockRequest('/debtor/1', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__.'/../resources/companies_service_match_trusted_source.json'))
        ));

        $this->mockRequestWith(
            '/debtor/c7be46c0-e049-4312-b274-258ec5aeeb70',
            file_get_contents(__DIR__.'/../resources/companies_service_match_trusted_source.json'),
            [],
            200,
            'PUT'
        );
    }

    /**
     * @Given I get from companies service :url endpoint response with status :statusCode and body
     */
    public function iGetFromCompaniesServiceEndpointResponseWithStatusAndBody($url, $statusCode, PyStringNode $response)
    {
        $this->mockRequest($url, new ResponseStack(
            new MockResponse($response, [], (int) $statusCode)
        ));
    }

    /**
     * @Given /^I get from companies service identify match from untrusted source$/
     */
    public function iGetFromCompaniesServiceIdentifyMatchFromUntrustedSource()
    {
        $this->mockRequest('/debtor/identify', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match_untrusted_source.json'))
        ));
    }

    /**
     * @Given /^I get from companies service synchronize merchant debtor good response and synchronized$/
     */
    public function iGetFromCompaniesServiceSynchronizedGoodResponseAndSynchronized()
    {
        $this->mockRequest('/debtor/1/synchronize', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match_trusted_source_is_synchronized.json'))
        ));
    }

    /**
     * @Given /^I get from companies service get debtors response$/
     */
    public function iGetFromCompaniesServiceAsAResponseWithMultipleIds()
    {
        $this->mockRequest('/debtors', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_get_multiple_results.json'))
        ));
    }

    /**
     * @Given I get from companies service a list of signatory-powers one signatory
     */
    public function iGetFromCompaniesServiceAListOfSignatoryPowersOneSignatory()
    {
        $this->mockRequest('/debtor/10/signatory-powers', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_signatory_powers_one_signatory.json'))
        ));
    }

    /**
     * @Given I get from companies service a list of signatory-powers
     */
    public function iGetFromCompaniesServiceAListOfSignatoryPowers()
    {
        $this->mockRequest('/debtor/10/signatory-powers', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_signatory_powers_list.json'))
        ));
    }

    /**
     * @Given I get from companies service a empty list of signatory-powers
     */
    public function iGetFromCompaniesServiceAEmptyListOfSignatoryPowers()
    {
        $this->mockRequest('/debtor/10/signatory-powers', new ResponseStack(
            new MockResponse("{}", [], 200)
        ));
    }

    /**
     * @Given /^I get from companies service identify no match and respond with suggestion$/
     */
    public function iGetFromCompaniesServiceIdentifyNoMatchAndRespondWithSuggestion()
    {
        $this->mockRequest('/debtor/identify', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_no_match_with_suggestion.json'), [], 404)
        ));
    }

    /**
     * @Given /^I get from companies service a successful response on create debtor call with body:$/
     */
    public function iGetFromCompaniesServiceASuccessfulResponseOnCreateDebtorCallWithBody(PyStringNode $body)
    {
        $this->mockRequest('/debtors', new MockResponse($body->__toString()));
    }

    /**
     * @Given I get from companies service an HTTP :status response from :method :endpoint with body:
     */
    public function iGetFromCompaniesASuccessfulResponseWithBody($status, $method, $endpoint, PyStringNode $body)
    {
        $this->mockRequest($endpoint, new MockResponse($body->__toString(), [], (int) $status));
    }

    /**
     * @Given I get from companies service an HTTP :status response from :method :endpoint
     */
    public function iGetFromCompaniesASuccessfulResponse($status, $method, $endpoint)
    {
        $this->mockRequest($endpoint, new MockResponse('{}', [], (int) $status));
    }
}
