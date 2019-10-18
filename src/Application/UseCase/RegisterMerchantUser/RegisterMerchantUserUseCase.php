<?php

namespace App\Application\UseCase\RegisterMerchantUser;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserEntityFactory;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;

class RegisterMerchantUserUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantRepository;

    private $merchantUserRepository;

    private $merchantUserEntityFactory;

    private $authenticationService;

    private $merchantUserRoleRepository;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserEntityFactory $merchantUserEntityFactory,
        AuthenticationServiceInterface $authenticationService,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantUserEntityFactory = $merchantUserEntityFactory;
        $this->authenticationService = $authenticationService;
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
    }

    public function execute(RegisterMerchantUserRequest $request): void
    {
        $this->validateRequest($request);

        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        if (!$merchant) {
            throw new MerchantNotFoundException();
        }

        $oauthUser = $this->authenticationService->createUser($request->getUserEmail(), $request->getUserPassword());
        $role = $this->merchantUserRoleRepository->getOneByUuid($request->getRoleUuid(), $request->getMerchantId());

        if (!$role) {
            throw new RoleNotFoundException();
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
    }
}
