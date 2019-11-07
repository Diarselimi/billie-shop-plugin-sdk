<?php

namespace App\Application\UseCase\GetMerchantUsers;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;

class GetMerchantUsersUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $userInvitationRepository;

    private $roleRepository;

    public function __construct(
        MerchantUserInvitationRepositoryInterface $userInvitationRepository,
        MerchantUserRoleRepositoryInterface $roleRepository
    ) {
        $this->userInvitationRepository = $userInvitationRepository;
        $this->roleRepository = $roleRepository;
    }

    public function execute(GetMerchantUsersRequest $request): GetMerchantUsersResponse
    {
        $this->validateRequest($request);

        $result = $this->userInvitationRepository->searchInvitedUsers(
            $request->getMerchantId(),
            $request->getOffset(),
            $request->getLimit(),
            $request->getSortBy(),
            $request->getSortDirection()
        );

        $roles = $this->roleRepository->findAllByMerchantId($request->getMerchantId());

        return new GetMerchantUsersResponse($result->getTotal(), $result->getItems(), iterator_to_array($roles));
    }
}
