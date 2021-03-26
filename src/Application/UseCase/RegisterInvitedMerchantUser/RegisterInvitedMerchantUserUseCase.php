<?php

namespace App\Application\UseCase\RegisterInvitedMerchantUser;

use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginResponse;
use App\Application\UseCase\RegisterInvitedMerchantUser\Exception\RegisterInvitedMerchantUserException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainEvent\MerchantOnboarding\MerchantOnboardingAdminUserCreated;
use App\DomainModel\Merchant\MerchantNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserAlreadyExistsException;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserEntityFactory;
use App\DomainModel\MerchantUser\MerchantUserLoginService;
use App\DomainModel\MerchantUser\MerchantUserRegistrationService;
use App\DomainModel\MerchantUser\MerchantUserService;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;
use Ozean12\Transfer\Message\MerchantUserInvitation\MerchantUserRegistered;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class RegisterInvitedMerchantUserUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private MerchantUserEntityFactory $merchantUserEntityFactory;

    private MerchantUserRegistrationService $registrationService;

    private MerchantUserLoginService $loginService;

    private MerchantUserService $userService;

    private EventDispatcherInterface $eventDispatcher;

    private MerchantRepositoryInterface $merchantRepository;

    private MessageBusInterface $messageBus;

    public function __construct(
        MerchantUserEntityFactory $merchantUserEntityFactory,
        MerchantUserRegistrationService $registrationService,
        MerchantUserLoginService $loginService,
        MerchantUserService $userService,
        EventDispatcherInterface $eventDispatcher,
        MerchantRepositoryInterface $merchantRepository,
        MessageBusInterface $messageBus
    ) {
        $this->merchantUserEntityFactory = $merchantUserEntityFactory;
        $this->loginService = $loginService;
        $this->registrationService = $registrationService;
        $this->userService = $userService;
        $this->eventDispatcher = $eventDispatcher;
        $this->merchantRepository = $merchantRepository;
        $this->messageBus = $messageBus;
    }

    public function execute(RegisterInvitedMerchantUserRequest $request): MerchantUserLoginResponse
    {
        $this->validateRequest($request);

        $invitation = $request->getInvitation();
        $merchantUser = $this->registerUser($request, $invitation);
        $email = $invitation->getEmail();
        $login = $this->loginService->login($email, $request->getPassword());
        $merchantUserResponse = $this->userService->getUser($merchantUser->getUuid(), $email);
        $this->eventDispatcher->dispatch(new MerchantOnboardingAdminUserCreated($merchantUser->getMerchantId()));

        $merchant = $this->merchantRepository->getOneById($merchantUser->getMerchantId());
        if ($merchant === null) {
            throw new MerchantNotFoundException(
                sprintf('Merchant with id %s not found', $merchantUser->getMerchantId())
            );
        }

        $this->messageBus->dispatch(
            (new MerchantUserRegistered())
                ->setMerchantUserUuid($merchantUser->getUuid())
                ->setMerchantPaymentUuid($merchant->getPaymentUuid())
                ->setFirstName($merchantUser->getFirstName())
                ->setLastName($merchantUser->getLastName())
                ->setToken($invitation->getToken())
        );

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
