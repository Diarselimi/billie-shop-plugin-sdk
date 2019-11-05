<?php

namespace App\Application\UseCase\MerchantUserLogin;

use App\Application\UseCase\GetMerchantUser\AbstractGetMerchantUserUseCase;
use App\Application\UseCase\GetMerchantUser\GetMerchantUserRequest;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;
use App\DomainModel\MerchantUser\MerchantUserPermissionsService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;

class MerchantUserLoginUseCase extends AbstractGetMerchantUserUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $authenticationService;

    public function __construct(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        AddressEntityFactory $addressEntityFactory,
        MerchantUserPermissionsService $merchantUserPermissionsService,
        AuthenticationServiceInterface $authenticationService
    ) {
        parent::__construct(
            $merchantUserRepository,
            $merchantRepository,
            $companiesService,
            $addressEntityFactory,
            $merchantUserPermissionsService
        );

        $this->authenticationService = $authenticationService;
    }

    public function execute(MerchantUserLoginRequest $request): MerchantUserLoginResponse
    {
        $this->validateRequest($request);

        try {
            $tokenInfo = $this->authenticationService->requestUserToken($request->getEmail(), $request->getPassword());
        } catch (AuthenticationServiceRequestException $exception) {
            throw new MerchantUserLoginException();
        }

        $tokenMetadata = $this->authenticationService->authorizeToken(
            $tokenInfo->getTokenType() . ' ' . $tokenInfo->getAccessToken()
        );

        if (!$tokenMetadata) {
            throw new MerchantUserLoginException("Invalid token metadata");
        }

        $user = $this->getUser(new GetMerchantUserRequest($tokenMetadata->getUserId()), $tokenMetadata->getEmail());

        return new MerchantUserLoginResponse($user, $tokenInfo->getAccessToken());
    }
}
