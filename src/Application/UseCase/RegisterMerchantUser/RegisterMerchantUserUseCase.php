<?php

namespace App\Application\UseCase\RegisterMerchantUser;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserEntityFactory;
use App\DomainModel\MerchantUser\MerchantUserRegistrationService;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUser\RoleNotFoundException;

class RegisterMerchantUserUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantRepository;

    private $merchantUserEntityFactory;

    private $merchantUserRoleRepository;

    private $registrationService;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        MerchantUserEntityFactory $merchantUserEntityFactory,
        MerchantUserRoleRepositoryInterface $merchantUserRoleRepository,
        MerchantUserRegistrationService $registrationService
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->merchantUserEntityFactory = $merchantUserEntityFactory;
        $this->merchantUserRoleRepository = $merchantUserRoleRepository;
        $this->registrationService = $registrationService;
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

        $merchantUser = $this->merchantUserEntityFactory->create(
            $request->getMerchantId(),
            $role->getId(),
            $request->getFirstName(),
            $request->getLastName(),
            $request->getPermissions() ?: []
        );

        $this->registrationService->registerUser(
            $merchantUser,
            $request->getUserEmail(),
            $request->getUserPassword()
        );
    }
}
