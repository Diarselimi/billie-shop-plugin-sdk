<?php

namespace spec\App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\ResetPassword\ResetPasswordException;
use App\Application\UseCase\ResetPassword\ResetPasswordRequest;
use App\Application\UseCase\ResetPassword\ResetPasswordUseCase;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResetPasswordControllerSpec extends ObjectBehavior
{
    public function let(ResetPasswordUseCase $useCase): void
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_execute_use_case(ResetPasswordUseCase $useCase): void
    {
        $password = 'someValidPassw0rd';
        $token = 'someToken';
        $useCase->execute(Argument::that(function (ResetPasswordRequest $request) use ($password, $token) {
            return $request->getPlainPassword() === $password
                && $request->getToken() === $token;
        }))->shouldBeCalledOnce();

        $request = Request::create('/');
        $request->request->set('password', $password);
        $request->request->set('token', $token);
        $this->execute($request);
    }

    public function it_should_throw_unauthorized_exception(ResetPasswordUseCase $useCase): void
    {
        $useCase->execute(Argument::cetera())->willThrow(ResetPasswordException::class);

        $this
            ->shouldThrow(HttpException::class)
            ->during('execute', [Request::create('/')]);
    }
}
