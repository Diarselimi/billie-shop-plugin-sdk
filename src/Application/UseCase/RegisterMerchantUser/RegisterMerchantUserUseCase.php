<?php

namespace App\Application\UseCase\RegisterMerchantUser;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceConflictRequestException;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserEntityFactory;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntityFactory;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;

class RegisterMerchantUserUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantRepository;

    private $merchantUserRepository;

    private $merchantUserEntityFactory;

    private $authenticationService;

    private $merchantUserRoleRepository;

    private $invitationRepository;

    private $invitationEntityFactory;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserEntityFactory $merchantUserEntityFactory,
        AuthenticationServiceInterface $authenticationService,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantUserInvitationRepositoryInterface $invitationRepository,
        MerchantUserInvitationEntityFactory $invitationEntityFactory
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantUserEntityFactory = $merchantUserEntityFactory;
        $this->authenticationService = $authenticationService;
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->invitationRepository = $invitationRepository;
        $this->invitationEntityFactory = $invitationEntityFactory;
    }

    public function execute(RegisterMerchantUserRequest $request): void
    {
        $this->validateRequest($request);

        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        if (!$merchant) {
            throw new MerchantNotFoundException();
        }

        $role = $this->merchantUserRoleRepository->getOneByUuid($request->getRoleUuid(), $request->getMerchantId());

        if (!$role) {
            throw new RoleNotFoundException();
        }

        try {
            $oauthUser = $this->authenticationService->createUser($request->getUserEmail(), $request->getUserPassword());
        } catch (AuthenticationServiceConflictRequestException $exception) {
            throw new MerchantUserAlreadyExistsException();
        }

        $merchantUser = $this->merchantUserEntityFactory->create(
            $request->getMerchantId(),
            $role->getId(),
            $oauthUser->getUserId(),
            $request->getFirstName(),
            $request->getLastName(),
            $request->getPermissions() ?: []
        );

        $this->merchantUserRepository->create($merchantUser);

        $invitation = $this->invitationEntityFactory->create(
            $request->getUserEmail(),
            $request->getMerchantId(),
            $role->getId(),
            $merchantUser->getId()
        )->setExpiresAt(new \DateTime());

        if (!$this->invitationRepository->existsForUser($merchantUser->getId())) {
            $this->invitationRepository->create($invitation);
        }
    }
}
