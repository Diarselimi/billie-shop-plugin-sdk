<?php

namespace App\DomainModel\MerchantUserInvitation;

use App\DomainModel\MerchantUser\MerchantUserRoleEntity;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;

class MerchantUserInvitationPersistenceService
{
    private $merchantUserRoleRepository;

    private $invitationRepository;

    private $invitationFactory;

    public function __construct(
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantUserInvitationRepositoryInterface $invitationRepository,
        MerchantUserInvitationEntityFactory $invitationFactory
    ) {
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->invitationRepository = $invitationRepository;
        $this->invitationFactory = $invitationFactory;
    }

    public function createInvitation(?MerchantUserRoleEntity $role, int $merchantId, string $email, ?\DateTime $expiresAt = null): MerchantUserInvitationEntity
    {
        if (!$role) {
            throw new RoleNotFoundException();
        }

        if ($this->invitationRepository->findValidByEmailAndMerchant($email, $merchantId)) {
            throw new MerchantUserInvitationAlreadyExistsException();
        }

        $invitation = $this->invitationFactory->create($email, $merchantId, $role->getId());
        if ($expiresAt) {
            $invitation->setExpiresAt($expiresAt);
        }
        $this->invitationRepository->create($invitation);

        return $invitation;
    }

    public function createInvitationByRoleName(string $roleName, int $merchantId, string $email, ?\DateTime $expiresAt = null): MerchantUserInvitationEntity
    {
        $role = $this->merchantUserRoleRepository->getOneByName($roleName, $merchantId);

        return $this->createInvitation($role, $merchantId, $email, $expiresAt);
    }
}
