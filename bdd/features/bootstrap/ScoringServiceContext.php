<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use donatj\MockWebServer\ResponseStack;
use donatj\MockWebServer\Response as MockResponse;

class ScoringServiceContext implements Context
{
    private const MOCK_SERVER_PORT = 8031;

    use MockServerTrait;

    public function __construct()
    {
        register_shutdown_function(
            function () {
                self::stopServer();
            }
        );
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
