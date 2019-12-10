<?php

namespace App\Application\UseCase\GetInvitedMerchantUser;

use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;

class GetInvitedMerchantUserUseCase
{
    private $roleRepository;

    public function __construct(MerchantUserRoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function execute(GetInvitedMerchantUserRequest $request): GetInvitedMerchantUserResponse
    {
        $invitation = $request->getInvitation();
        $role = $this->roleRepository->getOneById($invitation->getMerchantUserRoleId(), $invitation->getMerchantId());

        return new GetInvitedMerchantUserResponse($invitation->getEmail(), $role->isTcAcceptanceRequired());
    }
}
