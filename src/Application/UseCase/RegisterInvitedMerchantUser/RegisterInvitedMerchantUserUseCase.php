<?php

namespace App\Application\UseCase\RegisterInvitedMerchantUser;

use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginResponse;
use App\Application\UseCase\RegisterInvitedMerchantUser\Exception\RegisterInvitedMerchantUserException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\MerchantUserAlreadyExistsException;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserEntityFactory;
use App\DomainModel\MerchantUser\MerchantUserLoginService;
use App\DomainModel\MerchantUser\MerchantUserRegistrationService;
use App\DomainModel\MerchantUser\MerchantUserService;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;

class RegisterInvitedMerchantUserUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $merchantUserEntityFactory;

    private $registrationService;

    private $loginService;

    private $userService;

    public function __construct(
        MerchantUserEntityFactory $merchantUserEntityFactory,
        MerchantUserRegistrationService $registrationService,
        MerchantUserLoginService $loginService,
        MerchantUserService $userService
    ) {
        $this->merchantUserEntityFactory = $merchantUserEntityFactory;
        $this->loginService = $loginService;
        $this->registrationService = $registrationService;
        $this->userService = $userService;
    }

    public function execute(RegisterInvitedMerchantUserRequest $request): MerchantUserLoginResponse
    {
        $this->validateRequest($request);

        $merchantUser = $this->registerUser($request, $request->getInvitation());
        $email = $request->getInvitation()->getEmail();
        $login = $this->loginService->login($email, $request->getPassword());
        $merchantUserResponse = $this->userService->getUser($merchantUser->getUuid(), $email);

        return new MerchantUserLoginResponse($merchantUserResponse, $login->getAccessToken());
    }

    private function registerUser(
        RegisterInvitedMerchantUserRequest $request,
        MerchantUserInvitationEntity $invitation
    ): MerchantUserEntity {
        $merchantUser = $this->merchantUserEntityFactory->create(
            $invitation->getMerchantId(),
            $invitation->getMerchantUserRoleId(),
            $request->getFirstName(),
            $request->getLastName()
        );

        try {
            $this->registrationService->registerUser(
                $merchantUser,
                $invitation->getEmail(),
                $request->getPassword(),
                $invitation
            );
        } catch (MerchantUserAlreadyExistsException $exception) {
            throw new RegisterInvitedMerchantUserException('"Merchant user with the same login already exists', null, $exception);
        }

        return $merchantUser;
    }
}
