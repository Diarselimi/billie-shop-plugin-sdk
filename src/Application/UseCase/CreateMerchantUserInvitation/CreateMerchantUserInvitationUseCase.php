<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateMerchantUserInvitation;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationPersistenceService;

class CreateMerchantUserInvitationUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private const ROLE_BLACKLIST = [
        MerchantUserDefaultRoles::ROLE_NONE['name'],
        MerchantUserDefaultRoles::ROLE_ADMIN['name'],
        MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
    ];

    private MerchantUserRoleRepositoryInterface $merchantUserRoleRepository;

    private MerchantRepository $merchantRepository;

    private MerchantUserInvitationPersistenceService $invitationPersistenceService;

    public function __construct(
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantRepository $merchantRepository,
        MerchantUserInvitationPersistenceService $invitationPersistenceService
    ) {
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->merchantRepository = $merchantRepository;
        $this->invitationPersistenceService = $invitationPersistenceService;
    }

    public function execute(CreateMerchantUserInvitationRequest $request): CreateMerchantUserInvitationResponse
    {
        $this->validateRequest($request);

        $role = $this->merchantUserRoleRepository->getOneByUuid($request->getRoleUuid(), $request->getMerchantId());

        if ($role === null || in_array($role->getName(), self::ROLE_BLACKLIST)) {
            throw new RoleNotFoundException();
        }

        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());
        if ($merchant === null) {
            throw new MerchantNotFoundException();
        }

        $invitation = $this->invitationPersistenceService->createInvitation(
            $role,
            $merchant,
            $request->getEmail()
        );

        return new CreateMerchantUserInvitationResponse($invitation);
    }
}
