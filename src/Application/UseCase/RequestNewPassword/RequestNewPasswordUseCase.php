<?php

declare(strict_types=1);

namespace App\Application\UseCase\RequestNewPassword;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\PasswordResetRequest\PasswordResetRequestAnnouncer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class RequestNewPasswordUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait;
    use ValidatedUseCaseTrait;

    private $authenticationService;

    private $merchantUserRepository;

    private $merchantRepository;

    private $passwordResetRequestAnnouncer;

    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        PasswordResetRequestAnnouncer $passwordResetRequestAnnouncer
    ) {
        $this->authenticationService = $authenticationService;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantRepository = $merchantRepository;
        $this->passwordResetRequestAnnouncer = $passwordResetRequestAnnouncer;
    }

    public function execute(RequestNewPasswordRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $requestPasswordResetDTO = $this->authenticationService->requestNewPassword($request->getEmail());
        } catch (MerchantUserNotFoundException $exception) {
            $this->logWarning('User with email not found', [LoggingInterface::KEY_NAME => $request->getEmail()]);

            return;
        } catch (AuthenticationServiceRequestException $exception) {
            $this->logSuppressedException($exception, 'Failed to request new password');

            return;
        }

        $userUuid = $requestPasswordResetDTO->getUserUuid();
        $merchantUser = $this->merchantUserRepository->getOneByUuid($userUuid);
        if (!$merchantUser) {
            $this->logError('Smaug user not found in Paella', [LoggingInterface::KEY_UUID => $userUuid]);

            return;
        }
        $merchant = $this->merchantRepository->getOneById($merchantUser->getMerchantId());
        $this->passwordResetRequestAnnouncer->announcePasswordResetRequested(
            $merchant->getPaymentUuid(),
            $requestPasswordResetDTO->getToken(),
            $request->getEmail(),
            $merchantUser->getFirstName(),
            $merchantUser->getLastName(),
            $merchantUser->getUuid()
        );
    }
}
