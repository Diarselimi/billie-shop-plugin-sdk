<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantUserInvitation;

use App\DomainEvent\MerchantOnboarding\MerchantOnboardingAdminInvited;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantUser\MerchantUserRoleEntity;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use Ozean12\Transfer\Message\MerchantUserInvitation\MerchantUserInvitationCreated;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MerchantUserInvitationPersistenceService
{
    private MerchantUserRoleRepositoryInterface $merchantUserRoleRepository;

    private MerchantUserInvitationRepositoryInterface $invitationRepository;

    private MerchantUserInvitationEntityFactory $invitationFactory;

    private EventDispatcherInterface $eventDispatcher;

    private MessageBusInterface $messageBus;

    public function __construct(
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantUserInvitationRepositoryInterface $invitationRepository,
        MerchantUserInvitationEntityFactory $invitationFactory,
        EventDispatcherInterface $eventDispatcher,
        MessageBusInterface $messageBus
    ) {
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->invitationRepository = $invitationRepository;
        $this->invitationFactory = $invitationFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->messageBus = $messageBus;
    }

    public function createInvitation(?MerchantUserRoleEntity $role, MerchantEntity $merchant, string $email, ?\DateTime $expiresAt = null): MerchantUserInvitationEntity
    {
        if (!$role) {
            throw new RoleNotFoundException();
        }

        if ($this->invitationRepository->findByEmailAndMerchant($email, $merchant->getId(), true)) {
            throw new MerchantUserInvitationAlreadyExistsException();
        }

        $invitation = $this->invitationFactory->create($email, $merchant->getId(), $role->getId());
        if ($expiresAt) {
            $invitation->setExpiresAt($expiresAt);
        }
        $this->invitationRepository->create($invitation);

        $this->messageBus->dispatch(
            (new MerchantUserInvitationCreated())
                ->setMerchantPaymentUuid($merchant->getPaymentUuid())
                ->setEmail($email)
                ->setUserRoleName($role->getName())
                ->setToken($invitation->getToken())
        );

        if ($role->isAdmin()) {
            $this->eventDispatcher->dispatch(new MerchantOnboardingAdminInvited($merchant->getId()));
        }

        return $invitation;
    }

    public function createInvitationByRoleName(string $roleName, MerchantEntity $merchant, string $email, ?\DateTime $expiresAt = null): MerchantUserInvitationEntity
    {
        $role = $this->merchantUserRoleRepository->getOneByName($roleName, $merchant->getId());

        return $this->createInvitation($role, $merchant, $email, $expiresAt);
    }
}
