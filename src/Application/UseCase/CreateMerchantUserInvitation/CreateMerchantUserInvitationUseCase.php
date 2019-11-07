<?php

namespace App\Application\UseCase\CreateMerchantUserInvitation;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\MerchantUserDefaultRoles;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntityFactory;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;

class CreateMerchantUserInvitationUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantUserRoleRepository;

    private $invitationRepository;

    private $invitationFactory;

    private const ROLE_BLACKLIST = [
        MerchantUserDefaultRoles::ROLE_NONE['name'],
        MerchantUserDefaultRoles::ROLE_ADMIN['name'],
        MerchantUserDefaultRoles::ROLE_BILLIE_ADMIN['name'],
    ];

    public function __construct(
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantUserInvitationRepositoryInterface $invitationRepository,
        MerchantUserInvitationEntityFactory $invitationFactory
    ) {
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->invitationRepository = $invitationRepository;
        $this->invitationFactory = $invitationFactory;
    }

    public function execute(CreateMerchantUserInvitationRequest $request): CreateMerchantUserInvitationResponse
    {
        $this->validateRequest($request);

        if ($this->invitationRepository->findValidByEmailAndMerchant($request->getEmail(), $request->getMerchantId())) {
            throw new MerchantUserInvitationAlreadyExistsException();
        }

        $role = $this->merchantUserRoleRepository
            ->getOneByUuid($request->getRoleUuid(), $request->getMerchantId());

        if ($role === null || in_array($role->getName(), self::ROLE_BLACKLIST)) {
            throw new RoleNotFoundException();
        }

        $invitation = $this->invitationFactory->create(
            $request->getEmail(),
            $request->getMerchantId(),
            $role->getId()
        );

        $this->invitationRepository->create($invitation);

        return new CreateMerchantUserInvitationResponse($invitation);
    }
}
