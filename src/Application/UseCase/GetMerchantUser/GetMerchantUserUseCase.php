<?php

namespace App\Application\UseCase\GetMerchantUser;

use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;

class GetMerchantUserUseCase
{
    private $merchantUserRepository;

    public function __construct(MerchantUserRepositoryInterface $merchantUserRepository)
    {
        $this->merchantUserRepository = $merchantUserRepository;
    }

    public function execute(GetMerchantUserRequest $request): GetMerchantUserResponse
    {
        $merchantUser = $this->merchantUserRepository->getOneByUserId($request->getUserId());

        if (!$merchantUser) {
            throw new MerchantUserNotFoundException();
        }

        return new GetMerchantUserResponse($merchantUser->getId(), $merchantUser->getRoles());
    }
}
