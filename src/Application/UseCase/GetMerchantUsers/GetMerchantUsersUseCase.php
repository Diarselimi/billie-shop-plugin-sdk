<?php

namespace App\Application\UseCase\GetMerchantUsers;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleRepositoryInterface;
use App\DomainModel\MerchantUserInvitation\MerchantInvitedUserDTO;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationRepositoryInterface;

class GetMerchantUsersUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $userInvitationRepository;

    private $authenticationService;

    private $roleRepository;

    public function __construct(
        MerchantUserInvitationRepositoryInterface $userInvitationRepository,
        AuthenticationServiceInterface $authenticationService,
        MerchantUserRoleRepositoryInterface $roleRepository
    ) {
        $this->authenticationService = $authenticationService;
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
        $this->addEmails(... $result->getItems());

        return new GetMerchantUsersResponse($result->getTotal(), $result->getItems(), iterator_to_array($roles));
    }

    private function addEmails(MerchantInvitedUserDTO ...$users)
    {
        if (empty($users)) {
            return;
        }

        $usersByUuid = [];
        foreach ($users as $user) {
            if (!$user->getUserId()) {
                continue;
            }
            $usersByUuid[$user->getUserId()] = $user;
        }
        $oauthUsers = $this->authenticationService->getUsersByUuids(array_keys($usersByUuid));

        foreach ($usersByUuid as $uuid => $user) {
            if (!isset($oauthUsers[$uuid])) {
                continue;
            }
            $user->setEmail($oauthUsers[$uuid]->getUserEmail());
        }
    }
}
