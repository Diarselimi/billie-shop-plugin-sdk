<?php

namespace spec\App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\UpdateUserRole\UpdateUserRoleException;
use App\Application\UseCase\UpdateUserRole\UpdateUserRoleRequest;
use App\Application\UseCase\UpdateUserRole\UpdateUserRoleUseCase;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\Http\HttpConstantsInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateUserRoleControllerSpec extends ObjectBehavior
{
    public function let(UpdateUserRoleUseCase $useCase): void
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_execute_use_case(UpdateUserRoleUseCase $useCase): void
    {
        $merchantId = 1;
        $request = Request::create('');
        $request->attributes->set(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID, $merchantId);
        $userUuid = Uuid::uuid4()->toString();
        $roleUuid = Uuid::uuid4()->toString();
        $request->request->set('user_uuid', $userUuid);
        $request->request->set('role_uuid', $roleUuid);

        $useCase->execute(Argument::that(function (UpdateUserRoleRequest $updateUserRoleRequest) use (
            $merchantId,
            $userUuid,
            $roleUuid
        ) {
            return $updateUserRoleRequest->getMerchantId() === $merchantId
                && $updateUserRoleRequest->getUserUuid() === $userUuid
                && $updateUserRoleRequest->getRoleUuid() === $roleUuid;
        }));

        $this->execute($request);
    }

    public function it_should_throw_exception_when_user_not_found(UpdateUserRoleUseCase $useCase): void
    {
        $useCase->execute(Argument::cetera())->willThrow(MerchantUserNotFoundException::class);

        $this
            ->shouldThrow(NotFoundHttpException::class)
            ->during('execute', [Request::create('')]);
    }

    public function it_should_throw_exception_when_role_not_found(UpdateUserRoleUseCase $useCase): void
    {
        $useCase->execute(Argument::cetera())->willThrow(RoleNotFoundException::class);

        $this
            ->shouldThrow(NotFoundHttpException::class)
            ->during('execute', [Request::create('')]);
    }

    public function it_should_throw_exception_on_failure(UpdateUserRoleUseCase $useCase): void
    {
        $useCase->execute(Argument::cetera())->willThrow(UpdateUserRoleException::class);

        $this
            ->shouldThrow(BadRequestHttpException::class)
            ->during('execute', [Request::create('')]);
    }
}
