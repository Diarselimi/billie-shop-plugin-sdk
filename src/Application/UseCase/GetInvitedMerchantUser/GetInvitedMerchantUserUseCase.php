<?php

namespace App\Application\UseCase\GetInvitedMerchantUser;

class GetInvitedMerchantUserUseCase
{
    public function execute(GetInvitedMerchantUserRequest $request): GetInvitedMerchantUserResponse
    {
        return new GetInvitedMerchantUserResponse($request->getInvitation()->getEmail());
    }
}
