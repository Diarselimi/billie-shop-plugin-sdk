<?php

namespace App\Tests\Functional\Context;

use Behat\Behat\Context\Context;
use donatj\MockWebServer\Response;
use donatj\MockWebServer\ResponseStack;

class SepaServiceContext implements Context
{
    use MockServerTrait;

    public const DUMMY_SEPA_MANDATE_UUID = 'c7be46c0-e049-4312-b274-258ec5aeeb71';

    public function __construct()
    {
        $this->serviceBasePath = '/sepa/';
    }

    /**
     * @Given /^A sepa mandate sign call should be successful$/
     */
    public function aSepaMandateSignCallShouldBeSuccessful()
    {
        $this->mockRequest('/sepa-mandates/' . self::DUMMY_SEPA_MANDATE_UUID . '/sign', new ResponseStack(
            new Response('', [], 204)
        ));
    }
}
