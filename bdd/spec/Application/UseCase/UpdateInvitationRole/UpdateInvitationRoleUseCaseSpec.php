<?php

namespace spec\App\Application\UseCase\UpdateInvitationRole;

use App\Application\UseCase\UpdateInvitationRole\UpdateInvitationRoleException;
use App\Application\UseCase\UpdateInvitationRole\UpdateInvitationRoleRequest;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserRoleEntity;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;
use App\Http\Authentication\MerchantUser;
use App\Http\Authentication\UserProvider;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateInvitationRoleUseCaseSpec extends ObjectBehavior
{
    public function let(
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        UserProvider $userProvider,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith(...func_get_args());

        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());
        $this->setValidator($validator);
    }

    public function it_should_assign_role_to_invitation(
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        UserProvider $userProvider,
        MerchantUser $merchantUser
    ): void {
        $roleUuid = Uuid::uuid4()->toString();
        $currentRoleId = 1;
        $merchantId = 2;
        $invitationToUpdateId = 3;
        $newRoleId = 4;
        $email = 'test@billie.dev';
        $invitationToUpdate = (new MerchantUserInvitationEntity())
            ->setId($invitationToUpdateId)
            ->setMerchantUserRoleId($currentRoleId);
        $currentUser = (new MerchantUserEntity())->setUuid(Uuid::uuid4()->toString());
        $merchantUser->getUserEntity()->willReturn($currentUser);
        $newRole = (new MerchantUserRoleEntity())
            ->setId($newRoleId)
            ->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $merchantUserInvitationRepository->findByEmailAndMerchant($email, $merchantId, false)->willReturn($invitationToUpdate);
        $merchantUserRoleRepository->getOneByUuid($roleUuid)->willReturn($newRole);
        $merchantUserRoleRepository->getOneById($currentRoleId)->willReturn($currentRole);
        $userProvider->getMerchantUser()->willReturn($merchantUser);

        $merchantUserInvitationRepository->assignRoleToInvitation($invitationToUpdateId, $newRoleId)->shouldBeCalledOnce();

        $request = (new UpdateInvitationRoleRequest())
            ->setMerchantId($merchantId)
            ->setRoleUuid($roleUuid)
            ->setEmail($email);
        $this->execute($request);
    }

    public function it_should_throw_exception_when_invitation_not_found(
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository
    ): void {
        $merchantUserInvitationRepository->findByEmailAndMerchant(Argument::cetera())->willReturn(null);
        $request = (new UpdateInvitationRoleRequest())
            ->setMerchantId(1)
            ->setEmail('someEmail')
            ->setRoleUuid(Uuid::uuid4()->toString());

        $this
            ->shouldThrow(MerchantUserInvitationNotFoundException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_role_not_found(
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $invitationToUpdate = (new MerchantUserInvitationEntity())->setMerchantUserRoleId(1);
        $merchantUserInvitationRepository
            ->findByEmailAndMerchant(Argument::cetera())
            ->willReturn($invitationToUpdate);
        $merchantUserRoleRepository->getOneById(Argument::cetera())->willReturn($currentRole);
        $merchantUserRoleRepository->getOneByUuid(Argument::cetera())->willReturn(null);
        $request = (new UpdateInvitationRoleRequest())
            ->setMerchantId(1)
            ->setEmail('someEmail')
            ->setRoleUuid(Uuid::uuid4()->toString());

        $this
            ->shouldThrow(RoleNotFoundException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_admin_invitation_is_edited(
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $invitationToUpdate = (new MerchantUserInvitationEntity())->setMerchantUserRoleId(1);
        $merchantUserInvitationRepository
            ->findByEmailAndMerchant(Argument::cetera())
            ->willReturn($invitationToUpdate);
        $newRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $merchantUserRoleRepository->getOneByUuid(Argument::cetera())->willReturn($newRole);
        $currentRole = (new MerchantUserRoleEntity())
            ->setName(MerchantUserDefaultRoles::ROLE_ADMIN['name']);
        $merchantUserRoleRepository->getOneById(Argument::cetera())->willReturn($currentRole);
        $request = (new UpdateInvitationRoleRequest())
            ->setMerchantId(1)
            ->setEmail('someEmail')
            ->setRoleUuid(Uuid::uuid4()->toString());

        $this
            ->shouldThrow(UpdateInvitationRoleException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_editing_self(
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        UserProvider $userProvider,
        MerchantUser $merchantUser
    ): void {
        $invitationToUpdate = (new MerchantUserInvitationEntity())->setMerchantUserRoleId(1);
        $merchantUserInvitationRepository
            ->findByEmailAndMerchant(Argument::cetera())
            ->willReturn($invitationToUpdate);
        $newRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $merchantUserRoleRepository->getOneByUuid(Argument::cetera())->willReturn($newRole);
        $currentRole = (new MerchantUserRoleEntity())
            ->setName(MerchantUserDefaultRoles::ROLE_ADMIN['name']);
        $merchantUserRoleRepository->getOneById(Argument::cetera())->willReturn($currentRole);
        $userUuid = Uuid::uuid4()->toString();
        $request = (new UpdateInvitationRoleRequest())
            ->setMerchantId(1)
            ->setEmail('someEmail')
            ->setRoleUuid(Uuid::uuid4()->toString());
        $currentUser = (new MerchantUserEntity())->setUuid($userUuid);
        $merchantUser->getUserEntity()->willReturn($currentUser);
        $userProvider->getMerchantUser()->willReturn($merchantUser);

        $this
            ->shouldThrow(UpdateInvitationRoleException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_new_role_is_admin(
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $currentRoleId = 1;
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_SUPPORT['name']);
        $invitationToUpdate = (new MerchantUserInvitationEntity())->setMerchantUserRoleId(1);
        $merchantUserInvitationRepository
            ->findByEmailAndMerchant(Argument::cetera())
            ->willReturn($invitationToUpdate);
        $newRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_ADMIN['name']);
        $merchantUserRoleRepository->getOneById($currentRoleId)->willReturn($currentRole);
        $merchantUserRoleRepository->getOneByUuid(Argument::cetera())->willReturn($newRole);
        $request = (new UpdateInvitationRoleRequest())
            ->setMerchantId(1)
            ->setEmail('someEmail')
            ->setRoleUuid(Uuid::uuid4()->toString());

        $this
            ->shouldThrow(UpdateInvitationRoleException::class)
            ->during('execute', [$request]);
    }

    public function it_should_throw_exception_when_role_none_is_updated(
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ): void {
        $invitationToUpdate = (new MerchantUserInvitationEntity())->setMerchantUserRoleId(1);
        $merchantUserInvitationRepository
            ->findByEmailAndMerchant(Argument::cetera())
            ->willReturn($invitationToUpdate);
        $currentRole = (new MerchantUserRoleEntity())->setName(MerchantUserDefaultRoles::ROLE_NONE['name']);
        $merchantUserRoleRepository->getOneById(Argument::cetera())->willReturn($currentRole);

        $request = (new UpdateInvitationRoleRequest())
            ->setMerchantId(1)
            ->setEmail('someEmail')
            ->setRoleUuid(Uuid::uuid4()->toString());

        $this
            ->shouldThrow(MerchantUserInvitationNotFoundException::class)
            ->during('execute', [$request]);
    }
}
