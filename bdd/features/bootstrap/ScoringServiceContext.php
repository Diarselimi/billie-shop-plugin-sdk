<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use donatj\MockWebServer\ResponseStack;
use donatj\MockWebServer\Response as MockResponse;

class ScoringServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/jarvis/';
    }

    /**
     * @Given I get from scoring service good debtor scoring decision for debtor :uuid
     */
    public function iGetFromScoringServiceGoodDebtorScoringDecisionForDebtor($uuid)
    {
        $this->mockRequest("/debtor-scoring/$uuid", new ResponseStack(
            new MockResponse(file_get_contents(__DIR__.'/../resources/scoring_service_decision_good.json'))
        ));
    }

    /**
     * @Given I get from scoring service bad debtor scoring decision for debtor :uuid
     */
    public function iGetFromScoringServiceBadDebtorScoringDecisionForDebtor($uuid)
    {
        $this->mockRequest("/debtor-scoring/$uuid", new ResponseStack(
            new MockResponse(file_get_contents(__DIR__.'/../resources/scoring_service_decision_bad.json'))
        ));
    }
}
