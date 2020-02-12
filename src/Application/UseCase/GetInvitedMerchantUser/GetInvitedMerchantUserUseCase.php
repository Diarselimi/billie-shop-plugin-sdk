<?php

namespace App\Application\UseCase\GetInvitedMerchantUser;

use App\DomainEvent\MerchantOnboarding\MerchantOnboardingAdminInvitationOpened;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GetInvitedMerchantUserUseCase
{
    private $roleRepository;

    private $eventDispatcher;

    public function __construct(
        MerchantUserRoleRepositoryInterface $roleRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->roleRepository = $roleRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function execute(GetInvitedMerchantUserRequest $request): GetInvitedMerchantUserResponse
    {
        $invitation = $request->getInvitation();
        $role = $this->roleRepository->getOneById($invitation->getMerchantUserRoleId(), $invitation->getMerchantId());

        if ($role->isAdmin()) {
            $this->eventDispatcher->dispatch(new MerchantOnboardingAdminInvitationOpened($invitation->getMerchantId()));
        }

        return new GetInvitedMerchantUserResponse($invitation->getEmail(), $role->isTcAcceptanceRequired());
    }
}
