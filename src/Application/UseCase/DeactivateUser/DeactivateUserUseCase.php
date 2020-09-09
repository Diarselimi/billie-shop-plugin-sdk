<?php

declare(strict_types=1);

namespace App\Application\UseCase\DeactivateUser;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;

class DeactivateUserUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantUserRepository;

    private $merchantUserRoleRepository;

    private $merchantUserInvitationRepository;

    private $authenticationService;

    public function __construct(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantUserInvitationRepositoryInterface $merchantUserInvitationRepository,
        AuthenticationServiceInterface $authenticationService
    ) {
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->merchantUserInvitationRepository = $merchantUserInvitationRepository;
        $this->authenticationService = $authenticationService;
    }

    public function execute(DeactivateUserRequest $request): void
    {
        $this->validateRequest($request);

        if ($request->getUserUuid() === $request->getCurrentUserUuid()) {
            throw new DeactivateUserException('Users cannot deactivate themselves');
        }

        $merchantUser = $this->merchantUserRepository->getOneByUuidAndMerchantId(
            $request->getUserUuid(),
            $request->getMerchantId()
        );
        if (!$merchantUser) {
            throw new MerchantUserNotFoundException();
        }

        $currentRole = $this->merchantUserRoleRepository->getOneById($merchantUser->getRoleId());

        if ($currentRole->getName() === MerchantUserDefaultRoles::ROLE_ADMIN['name']) {
            throw new DeactivateUserException('Admin user cannot be deactivated');
        }

        try {
            $this->authenticationService->deactivateUser($request->getUserUuid());
        } catch (AuthenticationServiceRequestException $exception) {
            throw new DeactivateUserException('User deactivation failed');
        }

        if ($currentRole->getName() === MerchantUserDefaultRoles::ROLE_NONE['name']) {
            return;
        }

        $roleNone = $this->merchantUserRoleRepository->getOneByName(MerchantUserDefaultRoles::ROLE_NONE['name'], $request->getMerchantId());
        $this->merchantUserRepository->assignRoleToUser($merchantUser->getId(), $roleNone->getId());
    }
}
