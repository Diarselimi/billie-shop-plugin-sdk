<?php

namespace Billie\Sdk\Tests\Functional\Service\Request;

use Billie\Sdk\Model\Request\GetBankDataRequestModel;
use Billie\Sdk\Service\Request\GetBankDataRequest;
use PHPUnit\Framework\TestCase;

class GetBankDataRequestTest extends TestCase
{
    public function testExecute()
    {
        $requestService = new GetBankDataRequest();
        $responseModel = $requestService->execute(new GetBankDataRequestModel());

        static::assertIsArray($responseModel->getItems());
        static::assertGreaterThan(30, $responseModel->getItems());

        // currently this values come from a static file from the sdk, so we can compare exact names.
        static::assertEquals('Aachener Bausparkasse', $responseModel->getBankName('AABSDE31XXX'));
        static::assertEquals('DB Privat- und Firmenkundenbank (Deutsche Bank PGK)', $responseModel->getBankName('DEUTDEDB803'));
    }
}
