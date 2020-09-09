<?php

namespace spec\App\Http\Controller\PublicApi;

use App\Application\UseCase\DeactivateUser\DeactivateUserException;
use App\Application\UseCase\DeactivateUser\DeactivateUserRequest;
use App\Application\UseCase\DeactivateUser\DeactivateUserUseCase;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\Http\Authentication\MerchantUser;
use App\Http\Authentication\UserProvider;
use App\Http\HttpConstantsInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DeactivateUserControllerSpec extends ObjectBehavior
{
    public function let(DeactivateUserUseCase $useCase, UserProvider $userProvider): void
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_execute_use_case(
        DeactivateUserUseCase $useCase,
        UserProvider $userProvider,
        MerchantUser $merchantUser
    ): void {
        $merchantId = 1;
        $request = Request::create('');
        $request->attributes->set(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID, $merchantId);
        $userUuid = Uuid::uuid4()->toString();
        $currentUserUuid = Uuid::uuid4()->toString();
        $request->request->set('user_uuid', $userUuid);
        $merchantUser->getUserEntity()->willReturn((new MerchantUserEntity())->setUuid($currentUserUuid));
        $userProvider->getMerchantUser()->willReturn($merchantUser);

        $useCase->execute(Argument::that(function (DeactivateUserRequest $deactivateUserRequest) use (
            $merchantId, $userUuid, $currentUserUuid
        ) {
            return $deactivateUserRequest->getMerchantId() === $merchantId
                && $deactivateUserRequest->getUserUuid() === $userUuid
                && $deactivateUserRequest->getCurrentUserUuid() === $currentUserUuid;
        }));

        $this->execute($request, $userUuid);
    }

    public function it_should_throw_exception_on_failure(
        DeactivateUserUseCase $useCase,
        UserProvider $userProvider,
        MerchantUser $merchantUser
    ): void {
        $userUuid = Uuid::uuid4()->toString();
        $useCase->execute(Argument::cetera())->willThrow(DeactivateUserException::class);
        $merchantUser->getUserEntity()->willReturn((new MerchantUserEntity())->setUuid(Uuid::uuid4()->toString()));
        $userProvider->getMerchantUser()->willReturn($merchantUser);

        $this
            ->shouldThrow(BadRequestHttpException::class)
            ->during('execute', [Request::create(''), $userUuid]);
    }
}
