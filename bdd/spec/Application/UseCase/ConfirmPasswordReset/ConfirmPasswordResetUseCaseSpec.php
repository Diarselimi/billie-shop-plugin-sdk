<?php

namespace spec\App\Application\UseCase\ConfirmPasswordReset;

use App\Application\UseCase\ConfirmPasswordReset\ConfirmPasswordResetRequest;
use App\Application\UseCase\ConfirmPasswordReset\ValidPasswordResetTokenNotFoundException;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConfirmPasswordResetUseCaseSpec extends ObjectBehavior
{
    public function let(
        AuthenticationServiceInterface $authenticationService,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith(...func_get_args());
        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());
        $this->setValidator($validator);
    }

    public function it_should_validate_token(AuthenticationServiceInterface $authenticationService): void
    {
        $token = 'someToken';

        $authenticationService->confirmPasswordResetToken($token)->shouldBeCalledOnce();

        $this->execute(new ConfirmPasswordResetRequest($token));
    }

    public function it_should_throw_exception_on_authentication_service_exception(
        AuthenticationServiceInterface $authenticationService
    ): void {
        $authenticationService
            ->confirmPasswordResetToken(Argument::cetera())
            ->willThrow(AuthenticationServiceRequestException::class);
        $this
            ->shouldThrow(ValidPasswordResetTokenNotFoundException::class)
            ->during('execute', [new ConfirmPasswordResetRequest('someToken')]);
    }
}
