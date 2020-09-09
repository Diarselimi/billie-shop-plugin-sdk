<?php

namespace spec\App\Application\UseCase\DeactivateUser;

use App\Application\UseCase\DeactivateUser\DeactivateUserException;
use App\Application\UseCase\DeactivateUser\DeactivateUserRequest;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleEntity;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeactivateUserUseCaseSpec extends ObjectBehavior
{
    public function let(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        AuthenticationServiceInterface $authenticationService,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith(...func_get_args());

        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());
        $this->setValidator($validator);
    }

    public function it_should_deactivate_user(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $userUuid = Uuid::uuid4()->toString();
        $merchantId = 1;
        $userToDeactivate = (new MerchantUserEntity())
            ->setId($merchantId)
            ->setRoleId(1);
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);

        $merchantUserRepository->getOneByUuidAndMerchantId($userUuid, $merchantId)->willReturn($userToDeactivate);
        $merchantUserRoleRepository->getOneById(1)->willReturn($currentRole);
        $roleNone = (new MerchantUserRoleEntity())
            ->setId(1)
            ->setName(MerchantUserDefaultRoles::ROLE_NONE['name']);
        $merchantUserRoleRepository->getOneByName($roleNone->getName(), $merchantId)->willReturn($roleNone);

        $merchantUserRepository->assignRoleToUser($userToDeactivate->getId(), $roleNone->getId())->shouldBeCalledOnce();

        $request = (new DeactivateUserRequest())
            ->setMerchantId($merchantId)
            ->setUserUuid($userUuid)
            ->setCurrentUserUuid(Uuid::uuid4()->toString());
        $this->execute($request);
    }

    public function it_should_throw_exception_when_user_not_found(
        MerchantUserRepositoryInterface $merchantUserRepository
    ): void {
        $merchantUserRepository->getOneByUuidAndMerchantId(Argument::cetera())->willReturn(null);
        $request = (new DeactivateUserRequest())
            ->setMerchantId(1)
            ->setUserUuid(Uuid::uuid4()->toString());
        $this
            ->shouldThrow(MerchantUserNotFoundException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_admin_user_is_deactivated(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $userUuid = Uuid::uuid4()->toString();
        $merchantId = 1;
        $userToDeactivate = (new MerchantUserEntity())
            ->setId($merchantId)
            ->setRoleId(1);
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_ADMIN['name']);

        $merchantUserRepository->getOneByUuidAndMerchantId($userUuid, $merchantId)->willReturn($userToDeactivate);
        $merchantUserRoleRepository->getOneById(1)->willReturn($currentRole);

        $request = (new DeactivateUserRequest())
            ->setMerchantId($merchantId)
            ->setUserUuid($userUuid);

        $this
            ->shouldThrow(DeactivateUserException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_deactivating_self(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $userUuid = Uuid::uuid4()->toString();
        $merchantId = 1;
        $userToDeactivate = (new MerchantUserEntity())
            ->setId($merchantId)
            ->setRoleId(1);
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);

        $merchantUserRepository->getOneByUuidAndMerchantId($userUuid, $merchantId)->willReturn($userToDeactivate);
        $merchantUserRoleRepository->getOneById(1)->willReturn($currentRole);

        $request = (new DeactivateUserRequest())
            ->setMerchantId($merchantId)
            ->setUserUuid($userUuid)
            ->setCurrentUserUuid($userUuid);

        $this
            ->shouldThrow(DeactivateUserException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_external_service_fails(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        AuthenticationServiceInterface $authenticationService
    ): void {
        $userUuid = Uuid::uuid4()->toString();
        $merchantId = 1;
        $userToDeactivate = (new MerchantUserEntity())
            ->setId($merchantId)
            ->setRoleId(1);
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);

        $merchantUserRepository->getOneByUuidAndMerchantId($userUuid, $merchantId)->willReturn($userToDeactivate);
        $merchantUserRoleRepository->getOneById(1)->willReturn($currentRole);

        $authenticationService->deactivateUser($userUuid)->willThrow(AuthenticationServiceRequestException::class);

        $request = (new DeactivateUserRequest())
            ->setMerchantId($merchantId)
            ->setUserUuid($userUuid)
            ->setCurrentUserUuid(Uuid::uuid4()->toString());

        $this
            ->shouldThrow(DeactivateUserException::class)
            ->during('execute', [$request]);
    }

    public function it_should_success_when_user_already_deactivated(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $userUuid = Uuid::uuid4()->toString();
        $merchantId = 1;
        $currentRole = (new MerchantUserRoleEntity())
            ->setId(1)
            ->setName(MerchantUserDefaultRoles::ROLE_NONE['name']);
        $userToDeactivate = (new MerchantUserEntity())
            ->setId($merchantId)
            ->setRoleId(1);

        $merchantUserRepository->getOneByUuidAndMerchantId($userUuid, $merchantId)->willReturn($userToDeactivate);
        $merchantUserRoleRepository->getOneById($userToDeactivate->getRoleId())->willReturn($currentRole);

        $merchantUserRepository->assignRoleToUser($userToDeactivate->getId(), 1)->shouldNotBeCalled();

        $request = (new DeactivateUserRequest())
            ->setMerchantId($merchantId)
            ->setUserUuid($userUuid)
            ->setCurrentUserUuid(Uuid::uuid4()->toString());

        $this->execute($request);
    }
}
