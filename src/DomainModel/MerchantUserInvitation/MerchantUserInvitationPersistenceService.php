<?php

namespace App\DomainModel\MerchantUserInvitation;

use App\DomainEvent\MerchantOnboarding\MerchantOnboardingAdminInvited;
use App\DomainModel\MerchantUser\MerchantUserRoleEntity;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MerchantUserInvitationPersistenceService
{
    private $merchantUserRoleRepository;

    private $invitationRepository;

    private $invitationFactory;

    private $eventDispatcher;

    public function __construct(
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantUserInvitationRepositoryInterface $invitationRepository,
        MerchantUserInvitationEntityFactory $invitationFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->invitationRepository = $invitationRepository;
        $this->invitationFactory = $invitationFactory;
        $this->eventDispatcher = $eventDispatcher;
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

        if ($role->isAdmin()) {
            $this->eventDispatcher->dispatch(new MerchantOnboardingAdminInvited($merchantId));
        }

        return $invitation;
    }

    public function createInvitationByRoleName(string $roleName, int $merchantId, string $email, ?\DateTime $expiresAt = null): MerchantUserInvitationEntity
    {
        $role = $this->merchantUserRoleRepository->getOneByName($roleName, $merchantId);

        return $this->createInvitation($role, $merchantId, $email, $expiresAt);
    }
}
