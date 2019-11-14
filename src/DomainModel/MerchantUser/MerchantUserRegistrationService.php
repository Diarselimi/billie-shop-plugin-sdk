<?php

namespace App\DomainModel\MerchantUser;

use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntityFactory;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;

class MerchantUserRegistrationService
{
    private $merchantUserRepository;

    private $authenticationService;

    private $invitationRepository;

    private $invitationEntityFactory;

    public function __construct(
        MerchantUserRepositoryInterface $merchantUserRepository,
        AuthenticationServiceInterface $authenticationService,
        MerchantUserInvitationRepositoryInterface $invitationRepository,
        MerchantUserInvitationEntityFactory $invitationEntityFactory
    ) {
        $this->merchantUserRepository = $merchantUserRepository;
        $this->authenticationService = $authenticationService;
        $this->invitationRepository = $invitationRepository;
        $this->invitationEntityFactory = $invitationEntityFactory;
    }

    public function registerUser(
        MerchantUserEntity $user,
        string $email,
        string $password,
        MerchantUserInvitationEntity $invitation = null
    ): void {
        try {
            $oauthUser = $this->authenticationService->createUser($email, $password);
        } catch (AuthenticationServiceConflictRequestException $exception) {
            throw new MerchantUserAlreadyExistsException();
        }

        $user->setUuid($oauthUser->getUserId());
        $this->merchantUserRepository->create($user);

        if ($invitation) {
            $invitation
                ->setMerchantUserId($user->getId())
                ->setExpiresAt(new \DateTime())
            ;

            $this->invitationRepository->registerToMerchantUser($invitation);
        } else {
            $invitation = $this->invitationEntityFactory->create(
                $email,
                $user->getMerchantId(),
                $user->getRoleId(),
                $user->getId()
            )->setExpiresAt(new \DateTime());

            $this->invitationRepository->create($invitation);
        }
    }
}
