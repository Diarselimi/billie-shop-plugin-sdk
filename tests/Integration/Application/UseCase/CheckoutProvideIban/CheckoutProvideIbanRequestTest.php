<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\UseCase\CheckoutProvideIban;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\CheckoutProvideIban\CheckoutProvideIbanRequest;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\Tests\Integration\IntegrationTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckoutProvideIbanRequestTest extends IntegrationTestCase
{
    use ValidatedUseCaseTrait;

    /**
     * @test
     */
    public function validationShouldFailOnEmptyIbanProvided()
    {
        $request = new CheckoutProvideIbanRequest(Uuid::uuid4()->toString(), null, 'Edeka Co Kg');

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /**
     * @test
     */
    public function validationShouldFailOnInvalidIbanProvided()
    {
        $request = new CheckoutProvideIbanRequest(Uuid::uuid4()->toString(), 'DEINVALID', 'Edeka Co Kg');

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /**
     * @test
     */
    public function validationShouldFailOnNonGermanIbanProvided()
    {
        $request = new CheckoutProvideIbanRequest(Uuid::uuid4()->toString(), 'NL11RABO3382647974', 'Edeka Co Kg');

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /**
     * @test
     */
    public function validationShouldFailOnMissingOwner()
    {
        $request = new CheckoutProvideIbanRequest(Uuid::uuid4()->toString(), 'DE42500105172497563393', null);

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /**
     * @test
     */
    public function validationShouldPassIfValidIbanProvided()
    {
        $request = new CheckoutProvideIbanRequest(Uuid::uuid4()->toString(), 'DE42500105172497563393', 'Edeka Co Kg');

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);
        $this->validateRequest($request);

        self::assertTrue(true);
    }
}
