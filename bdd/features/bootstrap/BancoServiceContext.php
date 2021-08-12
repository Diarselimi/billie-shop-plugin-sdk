<?php

declare(strict_types=1);

namespace App\Tests\Functional\Context;

use Behat\Behat\Context\Context;
use donatj\MockWebServer\Response as MockResponse;
use donatj\MockWebServer\ResponseStack;

class BancoServiceContext implements Context
{
    use MockServerTrait;

    public function __construct()
    {
        $this->serviceBasePath = '/banco/';
    }

    /**
     * @Given /^I get from Banco service get bank good response$/
     */
    public function bancoServiceGetBankRespondedWithSuccess()
    {
        $this->mockRequest(
            '/api/v1/banks',
            new ResponseStack(
                new MockResponse(file_get_contents(__DIR__ . '/../resources/banco_get_bank.json'))
            )
        );
    }

    /**
     * @Given /^I get from Banco service search bank good response$/
     */
    public function bancoServiceSearchBankRespondedWithSuccess()
    {
        $bank = [
            'bic' => 'BICISHERE',
            'name' => 'Mocked Bank Name GmbH',
        ];

        $this->mockRequest(
            '/public/api/v1/banks/search',
            new ResponseStack(
                new MockResponse(json_encode(['banks' => [$bank]]))
            )
        );
    }
}
