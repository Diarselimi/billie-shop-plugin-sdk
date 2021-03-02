<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\UseCase\ResetPassword;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\ResetPassword\ResetPasswordRequest;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResetPasswordRequestTest extends IntegrationTestCase
{
    use ValidatedUseCaseTrait;

    public function setUp(): void
    {
        parent::setUp();
        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);
    }

    /**
     * @test
     * @dataProvider shouldValidatePasswordDataProvider
     */
    public function shouldValidatePassword(string $password, bool $shouldPassValidation): void
    {
        if (!$shouldPassValidation) {
            $this->expectException(RequestValidationException::class);
        }
        $request = new ResetPasswordRequest($password, 'sometoken');
        $this->validateRequest($request);

        if ($shouldPassValidation) {
            $this->assertTrue(true);
        }
    }

    public function shouldValidatePasswordDataProvider(): array
    {
        return [
            ['validPassword1', true],
            ['validpassword1', true],
            ['validPa$$word1', true],
            ['invalidpassword', false],
            ['invalidPassword', false],
            ['inVal1d', false],
            [str_repeat('aA0', 67), false],
        ];
    }
}
