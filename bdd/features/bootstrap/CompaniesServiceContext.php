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
        $this->startServer(self::MOCK_SERVER_PORT);

        register_shutdown_function(function () {
            $this->stopServer();
        });
    }

    /**
     * @AfterScenario
     */
    public function afterScenario()
    {
        $this->stopServer();
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
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json'))
        ));
    }

    /**
     * @Given /^I get from companies service get debtor response$/
     */
    public function iGetFromCompaniesServiceGetDebtorResponse()
    {
        $this->mockRequest('/debtor/1', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json'))
        ));
    }

    /**
     * @Given /^I get from companies service update debtor positive response$/
     */
    public function iGetFromCompaniesServiceUpdateDebtorPositiveResponse()
    {
        $this->mockRequest('/debtor/1', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json')),
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json'))
        ));
    }

    /**
     * @Given /^I get from companies service identify match and good decision response$/
     */
    public function iGetFromCompaniesServiceIdentifyMatchAndGoodDecisionResponse()
    {
        $this->mockRequest('/debtor/identify', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json'))
        ));

        $this->mockRequest('/debtor/1/is-eligible-for-pay-after-delivery', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_decision_good.json'))
        ));
    }

    /**
     * @Given /^I get from companies service identify match and bad decision response$/
     */
    public function iGetFromCompaniesServiceIdentifyMatchAndBadDecisionResponse()
    {
        $this->mockRequest('/debtor/identify', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json'))
        ));

        $this->mockRequest('/debtor/1/is-eligible-for-pay-after-delivery', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_decision_bad.json'))
        ));
    }

    /**
     * @Given /^I get from companies service identify v2 match response$/
     */
    public function iGetFromCompaniesServiceIdentifyV2MatchResponse()
    {
        $this->mockRequest('/debtor/identify/v2', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match_v2.json'))
        ));
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
}
