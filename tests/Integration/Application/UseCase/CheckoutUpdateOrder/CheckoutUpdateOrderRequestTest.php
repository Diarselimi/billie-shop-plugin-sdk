<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\UseCase\CheckoutUpdateOrder;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\CheckoutUpdateOrder\CheckoutUpdateOrderRequest;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\Tests\Helpers\FakeDataFiller;
use App\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckoutUpdateOrderRequestTest extends IntegrationTestCase
{
    use ValidatedUseCaseTrait;
    use FakeDataFiller;

    /**
     * @test
     */
    public function shouldValidateIfDurationIsNotValid()
    {
        $request = new CheckoutUpdateOrderRequest();
        $this->fillObject($request, true);
        $request->setDuration(99); //bad duration

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /**
     * @test
     */
    public function shouldValidateIfDurationIsValid()
    {
        $request = new CheckoutUpdateOrderRequest();
        $this->fillObject($request, true);
        $request->getBillingAddress()->setCountry('DE');
        $request->setDuration(90);

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->validateRequest($request);
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function shouldPassIfDurationIsNotProvided()
    {
        $request = new CheckoutUpdateOrderRequest();
        $this->fillObject($request, true);
        $request->getBillingAddress()->setCountry('DE');
        $request->setDuration(null);

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->validateRequest($request);

        $this->assertTrue(true);
    }
}
