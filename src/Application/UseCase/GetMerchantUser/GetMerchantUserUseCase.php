<?php

namespace App\Application\UseCase\GetMerchantUser;

use App\DomainModel\MerchantUser\MerchantUserDTO;
use App\DomainModel\MerchantUser\MerchantUserService;
use App\Http\Authentication\UserProvider;

class GetMerchantUserUseCase
{
    private $merchantUserService;

    private $userProvider;

    public function __construct(MerchantUserService $merchantUserService, UserProvider $userProvider)
    {
        $this->merchantUserService = $merchantUserService;
        $this->userProvider = $userProvider;
    }

    public function execute(GetMerchantUserRequest $request): MerchantUserDTO
    {
        return $this->merchantUserService->getUser(
            $request->getUuid(),
            $this->userProvider->getMerchantUser()->getEmail()
        );
    }
}
