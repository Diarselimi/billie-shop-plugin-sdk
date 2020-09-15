<?php

namespace spec\App\Http\Controller\PublicApi;

use App\Application\UseCase\ConfirmPasswordReset\ConfirmPasswordResetRequest;
use App\Application\UseCase\ConfirmPasswordReset\ConfirmPasswordResetUseCase;
use App\Application\UseCase\ConfirmPasswordReset\ValidPasswordResetTokenNotFoundException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ConfirmPasswordResetControllerSpec extends ObjectBehavior
{
    public function let(ConfirmPasswordResetUseCase $useCase): void
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_execute_use_case(ConfirmPasswordResetUseCase $useCase): void
    {
        $token = 'someToken';
        $useCase->execute(Argument::that(function (ConfirmPasswordResetRequest $request) use ($token) {
            return $request->getToken() === $token;
        }))->shouldBeCalledOnce();

        $request = Request::create('/');
        $request->query->set('token', $token);
        $this->execute($request);
    }

    public function it_should_throw_unauthorized_exception(ConfirmPasswordResetUseCase $useCase): void
    {
        $useCase->execute(Argument::cetera())->willThrow(ValidPasswordResetTokenNotFoundException::class);

        $this
            ->shouldThrow(UnauthorizedHttpException::class)
            ->during('execute', [Request::create('/')]);
    }
}
