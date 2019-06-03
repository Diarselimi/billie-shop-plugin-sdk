<?php

namespace App\Application\UseCase\RegisterMerchantUser;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserEntityFactory;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;

class RegisterMerchantUserUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantRepository;

    private $merchantUserRepository;

    private $merchantUserEntityFactory;

    private $authenticationService;

    public function __construct(
        MerchantRepositoryInterface $merchantRepository,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserEntityFactory $merchantUserEntityFactory,
        AuthenticationServiceInterface $authenticationService
    ) {
        $this->merchantRepository = $merchantRepository;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantUserEntityFactory = $merchantUserEntityFactory;
        $this->authenticationService = $authenticationService;
    }

    public function execute(RegisterMerchantUserRequest $request): void
    {
        $this->validateRequest($request);

        $merchant = $this->merchantRepository->getOneById($request->getMerchantId());

        if (!$merchant) {
            throw new MerchantNotFoundException();
        }

        $oauthUser = $this->authenticationService->createUser($request->getUserEmail(), $request->getUserPassword());

        $merchantUser = $this->merchantUserEntityFactory->create(
            $request->getMerchantId(),
            $oauthUser->getUserId(),
            $request->getRoles()
        );

        $this->merchantUserRepository->create($merchantUser);
    }
}
