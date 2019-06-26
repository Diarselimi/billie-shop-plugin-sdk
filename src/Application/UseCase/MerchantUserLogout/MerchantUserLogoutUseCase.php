<?php

namespace App\Application\UseCase\MerchantUserLogout;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\Infrastructure\Smaug\AuthenticationServiceException;

class MerchantUserLogoutUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $authenticationService;

    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function execute(MerchantUserLogoutRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $this->authenticationService->revokeToken($request->getUserAccessToken());
        } catch (AuthenticationServiceException $exception) {
            throw new MerchantUserLogoutException();
        }
    }
}
