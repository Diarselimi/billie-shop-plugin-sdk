<?php

namespace spec\App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\UpdateInvitationRole\UpdateInvitationRoleException;
use App\Application\UseCase\UpdateInvitationRole\UpdateInvitationRoleRequest;
use App\Application\UseCase\UpdateInvitationRole\UpdateInvitationRoleUseCase;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationNotFoundException;
use App\Http\HttpConstantsInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateInvitationRoleControllerSpec extends ObjectBehavior
{
    public function let(UpdateInvitationRoleUseCase $useCase): void
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_execute_use_case(UpdateInvitationRoleUseCase $useCase): void
    {
        $merchantId = 1;
        $request = Request::create('');
        $request->attributes->set(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID, $merchantId);
        $email = 'someEmail';
        $roleUuid = Uuid::uuid4()->toString();
        $request->request->set('email', $email);
        $request->request->set('role_uuid', $roleUuid);

        $useCase->execute(Argument::that(function (UpdateInvitationRoleRequest $updateInvitationRoleRequest) use (
            $merchantId,
            $email,
            $roleUuid
        ) {
            return $updateInvitationRoleRequest->getMerchantId() === $merchantId
                && $updateInvitationRoleRequest->getEmail() === $email
                && $updateInvitationRoleRequest->getRoleUuid() === $roleUuid;
        }));

        $this->execute($request);
    }

    public function it_should_throw_exception_when_invitation_not_found(UpdateInvitationRoleUseCase $useCase): void
    {
        $useCase->execute(Argument::cetera())->willThrow(MerchantUserInvitationNotFoundException::class);

        $this
            ->shouldThrow(NotFoundHttpException::class)
            ->during('execute', [Request::create('')]);
    }

    public function it_should_throw_exception_when_role_not_found(UpdateInvitationRoleUseCase $useCase): void
    {
        $useCase->execute(Argument::cetera())->willThrow(RoleNotFoundException::class);

        $this
            ->shouldThrow(NotFoundHttpException::class)
            ->during('execute', [Request::create('')]);
    }

    public function it_should_throw_exception_on_failure(UpdateInvitationRoleUseCase $useCase): void
    {
        $useCase->execute(Argument::cetera())->willThrow(UpdateInvitationRoleException::class);

        $this
            ->shouldThrow(BadRequestHttpException::class)
            ->during('execute', [Request::create('')]);
    }
}
