<?php

namespace spec\App\Http\Controller\PrivateApi;

use App\Application\UseCase\AuthorizeSandbox\AuthorizeSandboxUseCase;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthorizeSandboxControllerSpec extends ObjectBehavior
{
    public function let(
        AuthorizeSandboxUseCase $authorizeSandboxUseCase
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_throws_exception(
        AuthorizeSandboxUseCase $useCase,
        Request $request
    ) {
        $request->headers = new HeaderBag([]);
        $useCase
            ->execute('token')
            ->willThrow(new AuthenticationException);

        $this
            ->shouldThrow(new AccessDeniedHttpException('User can not be authenticated'))
            ->during('execute', [$request]);
    }
}
