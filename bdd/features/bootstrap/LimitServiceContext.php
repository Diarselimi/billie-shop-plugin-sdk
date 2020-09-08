<?php

declare(strict_types=1);

namespace App\Tests\Functional\Context;

use Behat\Behat\Context\Context;
use donatj\MockWebServer\ResponseStack;
use donatj\MockWebServer\Response as MockResponse;

class LimitServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/limes/';
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

    /**
     * @Given I get from limit service get debtor limit unsuccessful response for debtor :debtorCompanyUuid
     */
    public function iGetFromLimitServiceGetDebtorLimitUnsuccessfulResponseForDebtor($debtorCompanyUuid)
    {
        $this->mockRequest("/debtor-limit/$debtorCompanyUuid", new ResponseStack(
            new MockResponse(
                file_get_contents(__DIR__ . '/../resources/limit_service_get_debtor_limit_unsuccessful.json'),
                [],
                404
            )
        ));
    }

    /**
     * @Given /^Debtor update limit call succeeded$/
     */
    public function debtorUpdateLimitCallSucceeded()
    {
        $this->mockRequest('/debtor-limit/update', new ResponseStack(
            new MockResponse(
                file_get_contents(__DIR__ . '/../resources/limit_service_get_debtor_limit.json'),
                [],
                200
            )
        ));
    }

    /**
     * @Given /^I get from limit service create default debtor\-customer limit successful response$/
     */
    public function iGetFromLimitServiceCreateDefaultDebtorCustomerLimitSuccessfulResponse()
    {
        $this->mockRequest('/debtor-limit/default-customer-limit', new ResponseStack(
            new MockResponse('', [], 200)
        ));
    }
}
