<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use donatj\MockWebServer\ResponseStack;
use donatj\MockWebServer\Response as MockResponse;

class LimitServiceContext implements Context
{
    private const MOCK_SERVER_PORT = 8025;

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
     * @Given /^Debtor lock limit call succeeded$/
     */
    public function debtorLockLimitCallSucceeded()
    {
        $this->mockRequest('/debtor-limit/lock', new ResponseStack(
            new MockResponse('', [], 200)
        ));
    }

    /**
     * @Given /^Debtor has sufficient limit$/
     */
    public function debtorHasSufficientLimit()
    {
        $this->mockRequest('/debtor-limit/check', new ResponseStack(
            new MockResponse('{"is_sufficient": true}', [], 200)
        ));
    }

    /**
     * @Given /^Debtor has insufficient limit$/
     */
    public function debtorHasInsufficientLimit()
    {
        $this->mockRequest('/debtor-limit/check', new ResponseStack(
            new MockResponse('{"is_sufficient": false}', [], 200)
        ));
    }

    /**
     * @Given /^Debtor lock limit call failed$/
     */
    public function debtorLockLimitCallFailed()
    {
        $this->mockRequest('/debtor-limit/lock', new ResponseStack(
            new MockResponse('', [], 403)
        ));
    }

    /**
     * @Given /^Debtor release limit call succeeded$/
     */
    public function debtorReleaseLimitCallSucceeded()
    {
        $this->mockRequest('/debtor-limit/release', new ResponseStack(
            new MockResponse('', [], 200)
        ));
    }

    /**
     * @Given I get from limit service get debtor limit successful response for debtor :debtorCompanyUuid
     */
    public function iGetFromLimitServiceGetDebtorLimitSuccessfulResponseForDebtor($debtorCompanyUuid)
    {
        $this->mockRequest("/debtor-limit/$debtorCompanyUuid", new ResponseStack(
            new MockResponse(
                file_get_contents(__DIR__ . '/../resources/limit_service_get_debtor_limit.json'),
                [],
                200
            )
        ));
    }
}
