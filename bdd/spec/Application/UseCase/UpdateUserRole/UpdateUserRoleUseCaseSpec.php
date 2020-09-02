<?php

namespace spec\App\Application\UseCase\UpdateUserRole;

use App\Application\UseCase\UpdateUserRole\UpdateUserRoleException;
use App\Application\UseCase\UpdateUserRole\UpdateUserRoleRequest;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleEntity;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;
use App\Http\Authentication\MerchantUser;
use App\Http\Authentication\UserProvider;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateUserRoleUseCaseSpec extends ObjectBehavior
{
    public function let(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        UserProvider $userProvider,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith(...func_get_args());

        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());
        $this->setValidator($validator);
    }

    public function it_should_assign_role_to_user_and_invitation(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        UserProvider $userProvider,
        MerchantUser $merchantUser
    ): void {
        $userUuid = Uuid::uuid4()->toString();
        $roleUuid = Uuid::uuid4()->toString();
        $currentRoleId = 1;
        $merchantId = 2;
        $userToUpdateId = 3;
        $newRoleId = 4;
        $invitationId = 5;
        $userToUpdate = (new MerchantUserEntity())
            ->setId($userToUpdateId)
            ->setRoleId($currentRoleId);
        $currentUser = (new MerchantUserEntity())->setUuid(Uuid::uuid4()->toString());
        $merchantUser->getUserEntity()->willReturn($currentUser);
        $newRole = (new MerchantUserRoleEntity())
            ->setId($newRoleId)
            ->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $merchantUserRepository->getOneByUuidAndMerchantId($userUuid, $merchantId)->willReturn($userToUpdate);
        $merchantUserRoleRepository->getOneByUuid($roleUuid)->willReturn($newRole);
        $merchantUserRoleRepository->getOneById($currentRoleId)->willReturn($currentRole);
        $invitation = (new MerchantUserInvitationEntity())->setId($invitationId);
        $merchantUserInvitationRepository->findOneByMerchantUserId($userToUpdateId)->willReturn($invitation);
        $userProvider->getMerchantUser()->willReturn($merchantUser);

        $merchantUserRepository->assignRoleToUser($userToUpdateId, $newRoleId)->shouldBeCalledOnce();
        $merchantUserInvitationRepository->assignRoleToInvitation($invitationId, $newRoleId)->shouldBeCalledOnce();

        $request = (new UpdateUserRoleRequest())
            ->setMerchantId($merchantId)
            ->setRoleUuid($roleUuid)
            ->setUserUuid($userUuid);
        $this->execute($request);
    }

    public function it_should_throw_exception_when_user_not_found(
        MerchantUserRepositoryInterface $merchantUserRepository
    ): void {
        $merchantUserRepository->getOneByUuidAndMerchantId(Argument::cetera())->willReturn(null);
        $request = (new UpdateUserRoleRequest())
            ->setMerchantId(1)
            ->setUserUuid(Uuid::uuid4()->toString());
        $this
            ->shouldThrow(MerchantUserNotFoundException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_role_not_found(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $currentRoleId = 1;
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $merchantUserEntity = (new MerchantUserEntity())->setRoleId($currentRoleId);
        $merchantUserRepository->getOneByUuidAndMerchantId(Argument::cetera())->willReturn($merchantUserEntity);
        $merchantUserRoleRepository->getOneById($currentRoleId)->willReturn($currentRole);
        $merchantUserRoleRepository->getOneByUuid(Argument::cetera())->willReturn(null);
        $request = (new UpdateUserRoleRequest())
            ->setMerchantId(1)
            ->setRoleUuid(Uuid::uuid4()->toString())
            ->setUserUuid(Uuid::uuid4()->toString());

        $this
            ->shouldThrow(RoleNotFoundException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_admin_user_is_edited(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $userToUpdate = (new MerchantUserEntity())->setRoleId(1);
        $merchantUserRepository->getOneByUuidAndMerchantId(Argument::cetera())->willReturn($userToUpdate);
        $newRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $merchantUserRoleRepository->getOneByUuid(Argument::cetera())->willReturn($newRole);
        $currentRole = (new MerchantUserRoleEntity())
            ->setName(MerchantUserDefaultRoles::ROLE_ADMIN['name']);
        $merchantUserRoleRepository->getOneById(Argument::cetera())->willReturn($currentRole);
        $request = (new UpdateUserRoleRequest())
            ->setMerchantId(1)
            ->setRoleUuid(Uuid::uuid4()->toString())
            ->setUserUuid(Uuid::uuid4()->toString());

        $this
            ->shouldThrow(UpdateUserRoleException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_editing_self(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        UserProvider $userProvider,
        MerchantUser $merchantUser
    ): void {
        $userToUpdate = (new MerchantUserEntity())->setRoleId(1);
        $merchantUserRepository->getOneByUuidAndMerchantId(Argument::cetera())->willReturn($userToUpdate);
        $newRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $merchantUserRoleRepository->getOneByUuid(Argument::cetera())->willReturn($newRole);
        $currentRole = (new MerchantUserRoleEntity())
            ->setName(MerchantUserDefaultRoles::ROLE_ADMIN['name']);
        $merchantUserRoleRepository->getOneById(Argument::cetera())->willReturn($currentRole);
        $userUuid = Uuid::uuid4()->toString();
        $request = (new UpdateUserRoleRequest())
            ->setMerchantId(1)
            ->setRoleUuid(Uuid::uuid4()->toString())
            ->setUserUuid($userUuid);
        $currentUser = (new MerchantUserEntity())->setUuid($userUuid);
        $merchantUser->getUserEntity()->willReturn($currentUser);
        $userProvider->getMerchantUser()->willReturn($merchantUser);

        $this
            ->shouldThrow(UpdateUserRoleException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_new_role_is_admin(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $currentRoleId = 1;
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $userToUpdate = (new MerchantUserEntity())->setRoleId(1);
        $merchantUserRepository->getOneByUuidAndMerchantId(Argument::cetera())->willReturn($userToUpdate);
        $newRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_ADMIN['name']);
        $merchantUserRoleRepository->getOneById($currentRoleId)->willReturn($currentRole);
        $merchantUserRoleRepository->getOneByUuid(Argument::cetera())->willReturn($newRole);
        $userUuid = Uuid::uuid4()->toString();
        $request = (new UpdateUserRoleRequest())
            ->setMerchantId(1)
            ->setRoleUuid(Uuid::uuid4()->toString())
            ->setUserUuid($userUuid);

        $this
            ->shouldThrow(UpdateUserRoleException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_role_none_is_updated(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $userToUpdate = (new MerchantUserEntity())->setRoleId(1);
        $merchantUserRepository->getOneByUuidAndMerchantId(Argument::cetera())->willReturn($userToUpdate);
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_NONE['name']);
        $merchantUserRoleRepository->getOneById(Argument::cetera())->willReturn($currentRole);

        $request = (new UpdateUserRoleRequest())
            ->setMerchantId(1)
            ->setRoleUuid(Uuid::uuid4()->toString())
            ->setUserUuid(Uuid::uuid4()->toString());

        $this
            ->shouldThrow(MerchantUserNotFoundException::class)
            ->during('execute', [$request]);
    }
}
