<?php

namespace App\Application\UseCase\MerchantUserLogin;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\MerchantUserLoginService;
use App\DomainModel\MerchantUser\MerchantUserService;

class MerchantUserLoginUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantUserService;

    private $loginService;

    public function __construct(
        MerchantUserService $merchantUserService,
        MerchantUserLoginService $loginService
    ) {
        $this->merchantUserService = $merchantUserService;
        $this->loginService = $loginService;
    }

    public function execute(MerchantUserLoginRequest $request): MerchantUserLoginResponse
    {
        $this->validateRequest($request);

        $login = $this->loginService->login($request->getEmail(), $request->getPassword());
        $user = $this->merchantUserService->getUser($login->getUserUuid(), $login->getEmail());

        return new MerchantUserLoginResponse($user, $login->getAccessToken());
    }
}
