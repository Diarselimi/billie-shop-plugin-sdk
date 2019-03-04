<?php

use Behat\Behat\Context\Context;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;
use Symfony\Component\HttpKernel\KernelInterface;

class CompaniesServiceContext implements Context
{
    use MockServerTrait;

    public function __construct(KernelInterface $kernel)
    {
        $this->setServer($kernel);
    }

    /**
     * @Given /^I get from companies service identify no match response$/
     */
    public function iGetFromCompaniesServiceIdentifyNoMatchResponse()
    {
        $this->setMock('/debtor/identify', new ResponseStack(new MockResponse('', [], 404)));
    }

    /**
     * @Given /^I get from companies service identify match response$/
     */
    public function iGetFromCompaniesServiceIdentifyMatchResponse()
    {
        $this->setMock('/debtor/identify', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json'))
        ));
    }

    /**
     * @Given /^I get from companies service get debtor response$/
     */
    public function iGetFromCompaniesServiceGetDebtorResponse()
    {
        $this->setMock('/debtor/1', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json'))
        ));
    }

    /**
     * @Given /^I get from companies service update debtor positive response$/
     */
    public function iGetFromCompaniesServiceUpdateDebtorPositiveResponse()
    {
        $this->setMock('/debtor/1', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json')),
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json'))
        ));
    }

    /**
     * @Given /^I get from companies service identify match and good decision response$/
     */
    public function iGetFromCompaniesServiceIdentifyMatchAndGoodDecisionResponse()
    {
        $this->setMock('/debtor/identify', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json'))
        ));

        $this->setMock('/debtor/1/is-eligible-for-pay-after-delivery', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_decision_good.json'))
        ));
    }

    /**
     * @Given /^I get from companies service identify match and bad decision response$/
     */
    public function iGetFromCompaniesServiceIdentifyMatchAndBadDecisionResponse()
    {
        $this->setMock('/debtor/identify', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match.json'))
        ));

        $this->setMock('/debtor/1/is-eligible-for-pay-after-delivery', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_decision_bad.json'))
        ));
    }

    /**
     * @Given /^I get from companies service identify v2 match response$/
     */
    public function iGetFromCompaniesServiceIdentifyV2MatchResponse()
    {
        $this->setMock('/debtor/identify/v2', new ResponseStack(
            new MockResponse(file_get_contents(__DIR__ . '/../resources/companies_service_match_v2.json'))
        ));
    }
}
