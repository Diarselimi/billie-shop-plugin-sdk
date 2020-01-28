<?php

declare(strict_types=1);

namespace App\Application\UseCase\StartIdentityVerificationBySignatoryPower;

use App\Application\UseCase\StartIdentityVerificationResponse;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\IdentityVerification\IdentityVerificationServiceException;
use App\DomainModel\IdentityVerification\IdentityVerificationServiceInterface;
use App\DomainModel\IdentityVerification\IdentityVerificationStartRequestDTO;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;

class StartIdentityVerificationBySignatoryPowerUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $identityVerificationService;

    private $merchantUserRepository;

    private $stepTransitionService;

    public function __construct(
        IdentityVerificationServiceInterface $identityVerificationService,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantStepTransitionService $stepTransitionService
    ) {
        $this->identityVerificationService = $identityVerificationService;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->stepTransitionService = $stepTransitionService;
    }

    public function execute(StartIdentityVerificationBySignatoryPowerRequest $request): StartIdentityVerificationResponse
    {
        $this->validateRequest($request);

        if ($request->getSignatoryPowerDTO()->isIdentityVerified()) {
            throw new SignatoryPowerAlreadyIdentifiedException();
        }

        $identificationRequest = $this->buildIdentityVerificationStartRequestDTO($request);

        try {
            $identificationResponse = $this->identityVerificationService->startVerificationCase($identificationRequest);
        } catch (IdentityVerificationServiceException $exception) {
            throw new StartIdentityVerificationBySignatoryPowerException();
        }

        return new StartIdentityVerificationResponse($identificationResponse->getUrl());
    }

    private function buildIdentityVerificationStartRequestDTO(StartIdentityVerificationBySignatoryPowerRequest $request): IdentityVerificationStartRequestDTO
    {
        return (new IdentityVerificationStartRequestDTO())
            ->setSignatoryPowerUuid($request->getSignatoryPowerDTO()->getUuid())
            ->setFirstName($request->getSignatoryPowerDTO()->getFirstName())
            ->setLastName($request->getSignatoryPowerDTO()->getLastName())
            ->setEmail($request->getSignatoryPowerDTO()->getEmail())
            ->setRedirectUrlCouponRequested($request->getRedirectUrlCouponRequested())
            ->setRedirectUrlReviewPending($request->getRedirectUrlReviewPending())
            ->setRedirectUrlDeclined($request->getRedirectUrlDeclined());
    }
}
