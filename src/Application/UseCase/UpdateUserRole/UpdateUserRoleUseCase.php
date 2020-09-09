<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateUserRole;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\Http\Authentication\UserProvider;

class UpdateUserRoleUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantUserRepository;

    private $merchantUserRoleRepository;

    private $merchantUserInvitationRepository;

    private $userProvider;

    public function __construct(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        UserProvider $userProvider
    ) {
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->userProvider = $userProvider;
    }

    public function execute(UpdateUserRoleRequest $request): void
    {
        $this->validateRequest($request);

        $merchantUser = $this->merchantUserRepository->getOneByUuidAndMerchantId(
            $request->getUserUuid(),
            $request->getMerchantId()
        );
        if (!$merchantUser) {
            throw new MerchantUserNotFoundException();
        }

        $currentRole = $this->merchantUserRoleRepository->getOneById($merchantUser->getRoleId());
        $isUserDeactivated = $currentRole->getName() === MerchantUserDefaultRoles::ROLE_NONE['name'];
        if ($isUserDeactivated) {
            throw new MerchantUserNotFoundException();
        }

        $newRole = $this->merchantUserRoleRepository->getOneByUuid($request->getRoleUuid());
        if (!$newRole) {
            throw new RoleNotFoundException();
        }

        if ($newRole->getName() === MerchantUserDefaultRoles::ROLE_ADMIN['name']) {
            throw new UpdateUserRoleException('User cannot be updated to admin');
        }

        if ($currentRole->getName() === MerchantUserDefaultRoles::ROLE_ADMIN['name']) {
            throw new UpdateUserRoleException('Users with role admin cannot be edited');
        }

        if ($request->getUserUuid() === $this->userProvider->getMerchantUser()->getUserEntity()->getUuid()) {
            throw new UpdateUserRoleException('Users cannot edit themselves');
        }

        $this->merchantUserRepository->assignRoleToUser($merchantUser->getId(), $newRole->getId());
    }
}
