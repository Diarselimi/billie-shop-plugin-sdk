<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateInvitationRole;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;
use App\Http\Authentication\UserProvider;

class UpdateInvitationRoleUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantUserInvitationRepository;

    private $merchantUserRoleRepository;

    private $userProvider;

    public function __construct(
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        UserProvider $userProvider
    ) {
        $this->merchantUserInvitationRepository = $merchantUserInvitationRepository;
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->userProvider = $userProvider;
    }

    public function execute(UpdateInvitationRoleRequest $request): void
    {
        $this->validateRequest($request);

        $invitation = $this->merchantUserInvitationRepository->findByEmailAndMerchant(
            $request->getEmail(),
            $request->getMerchantId(),
            false
        );
        if (!$invitation) {
            throw new MerchantUserInvitationNotFoundException();
        }

        $currentRole = $this->merchantUserRoleRepository->getOneById($invitation->getMerchantUserRoleId());
        $isInvitationUserDeactivated = $currentRole->getName() === MerchantUserDefaultRoles::ROLE_NONE['name'];
        if ($isInvitationUserDeactivated) {
            throw new MerchantUserInvitationNotFoundException();
        }

        $newRole = $this->merchantUserRoleRepository->getOneByUuid($request->getRoleUuid());
        if (!$newRole) {
            throw new RoleNotFoundException();
        }

        if ($newRole->getName() === MerchantUserDefaultRoles::ROLE_ADMIN['name']) {
            throw new UpdateInvitationRoleException('User cannot be updated to admin');
        }

        if ($currentRole->getName() === MerchantUserDefaultRoles::ROLE_ADMIN['name']) {
            throw new UpdateInvitationRoleException('Invitations with role admin cannot be edited');
        }

        $this->merchantUserInvitationRepository->assignRoleToInvitation($invitation->getId(), $newRole->getId());
    }
}
