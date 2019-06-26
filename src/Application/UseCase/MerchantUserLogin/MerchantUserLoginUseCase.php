<?php

namespace App\Application\UseCase\MerchantUserLogin;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\Infrastructure\Smaug\AuthenticationServiceException;

class MerchantUserLoginUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantUserRepository;

    private $authenticationService;

    public function __construct(
        MerchantUserRepositoryInterface $merchantUserRepository,
        AuthenticationServiceInterface $authenticationService
    ) {
        $this->merchantUserRepository = $merchantUserRepository;
        $this->authenticationService = $authenticationService;
    }

    public function execute(MerchantUserLoginRequest $request): MerchantUserLoginResponse
    {
        $this->validateRequest($request);

        try {
            $tokenInfo = $this->authenticationService->requestUserToken($request->getEmail(), $request->getPassword());
        } catch (AuthenticationServiceException $exception) {
            throw new MerchantUserLoginException();
        }

        $tokenMetadata = $this->authenticationService->authorizeToken(
            $tokenInfo->getTokenType() . ' ' . $tokenInfo->getAccessToken()
        );

        if (!$tokenMetadata) {
            throw new MerchantUserLoginException();
        }

        $merchantUser = $this->merchantUserRepository->getOneByUserId($tokenMetadata->getUserId());

        if (!$merchantUser) {
            throw new MerchantUserLoginException();
        }

        return new MerchantUserLoginResponse($tokenInfo->getAccessToken(), $merchantUser->getRoles());
    }
}
