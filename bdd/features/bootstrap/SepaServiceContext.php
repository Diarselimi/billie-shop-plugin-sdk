<?php

declare(strict_types=1);

namespace App\Tests\Functional\Context;

use Behat\Behat\Context\Context;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;

class SepaServiceContext implements Context
{
    use MockServerTrait;

    public const DUMMY_SEPA_MANDATE_UUID = 'c7be46c0-e049-4312-b274-258ec5aeeb71';

    /**
     * @Given /^A sepa mandate sign call should be successful$/
     */
    public function aSepaMandateSignCallShouldBeSuccessful()
    {
        $this->mockRequest('/sepa-mandates/' . self::DUMMY_SEPA_MANDATE_UUID . '/sign', new ResponseStack(
            new MockResponse('', [], 204)
        ));
    }

    /**
     * @Given /^I get from Sepa service generate mandate good response$/
     */
    public function sepaServiceGenerateMandateRespondedWithSuccess()
    {
        $this->mockRequest(
            '/sepa-mandates',
            new ResponseStack(
                new MockResponse(
                    file_get_contents(__DIR__ . '/../resources/sepa_mandate.json'),
                    ['Content-Type' => 'application/json']
                )
            )
        );
    }

    /**
     * @Given /^I get from Sepa service get mandate valid response$/
     */
    public function sepaServiceGetMandate()
    {
        $this->mockRequest('/sepa-mandates/' . self::DUMMY_SEPA_MANDATE_UUID . '', new ResponseStack(
            new MockResponse(
                file_get_contents(__DIR__ . '/../resources/sepa_mandate_valid.json'),
                ['Content-Type' => 'application/json']
            )
        ));
    }

    /**
     * @Given I get from Sepa service for :arg1 mandate valid response
     */
    public function iGetFromSepaServiceMandateValidResponse($arg1)
    {
        $this->mockRequest('/sepa-mandates/' . $arg1 . '', new ResponseStack(
            new MockResponse(
                file_get_contents(__DIR__ . '/../resources/sepa_mandate_valid.json'),
                ['Content-Type' => 'application/json']
            )
        ));
    }
}
