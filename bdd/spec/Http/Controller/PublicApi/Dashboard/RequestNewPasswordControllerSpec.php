<?php

namespace spec\App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\RequestNewPassword\RequestNewPasswordRequest;
use App\Application\UseCase\RequestNewPassword\RequestNewPasswordUseCase;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class RequestNewPasswordControllerSpec extends ObjectBehavior
{
    public function let(RequestNewPasswordUseCase $useCase): void
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_execute_use_case(RequestNewPasswordUseCase $useCase): void
    {
        $email = 'test@billie.dev';
        $useCase->execute(Argument::that(function (RequestNewPasswordRequest $request) use ($email) {
            return $request->getEmail() === $email;
        }))->shouldBeCalledOnce();

        $request = Request::create('/');
        $request->request->set('email', $email);
        $this->execute($request);
    }
}
