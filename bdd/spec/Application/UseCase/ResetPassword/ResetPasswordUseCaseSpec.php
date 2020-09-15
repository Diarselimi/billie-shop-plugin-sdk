<?php

namespace spec\App\Application\UseCase\ResetPassword;

use App\Application\UseCase\ResetPassword\ResetPasswordException;
use App\Application\UseCase\ResetPassword\ResetPasswordRequest;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResetPasswordUseCaseSpec extends ObjectBehavior
{
    public function let(
        AuthenticationServiceInterface $authenticationService,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith(...func_get_args());
        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());
        $this->setValidator($validator);
    }

    public function it_should_reset_password(AuthenticationServiceInterface $authenticationService): void
    {
        $token = 'someToken';
        $password = 'someValidPassw0rd';

        $authenticationService->resetPassword($password, $token)->shouldBeCalledOnce();

        $this->execute(new ResetPasswordRequest($password, $token));
    }

    public function it_should_throw_exception_on_authentication_service_exception(
        AuthenticationServiceInterface $authenticationService
    ): void {
        $authenticationService
            ->resetPassword(Argument::cetera())
            ->willThrow(AuthenticationServiceRequestException::class);
        $this
            ->shouldThrow(ResetPasswordException::class)
            ->during('execute', [
                new ResetPasswordRequest('someValidPassw0rd', 'someToken'),
            ]);
    }
}
