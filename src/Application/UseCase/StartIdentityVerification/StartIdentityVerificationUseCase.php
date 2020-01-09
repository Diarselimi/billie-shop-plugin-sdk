<?php

declare(strict_types=1);

namespace App\Application\UseCase\StartIdentityVerification;

use App\Application\Exception\MerchantOnboardingStepTransitionException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\IdentityVerification\IdentityVerificationServiceException;
use App\DomainModel\IdentityVerification\IdentityVerificationServiceInterface;
use App\DomainModel\IdentityVerification\IdentityVerificationStartRequestDTO;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepNotFoundException;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;

class StartIdentityVerificationUseCase implements ValidatedUseCaseInterface
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

    public function execute(StartIdentityVerificationRequest $request): StartIdentityVerificationResponse
    {
        $this->validateRequest($request);

        try {
            $this->stepTransitionService->transition(
                MerchantOnboardingStepEntity::STEP_IDENTITY_VERIFICATION,
                MerchantOnboardingStepTransitionEntity::TRANSITION_REQUEST_CONFIRMATION,
                $request->getMerchantPaymentUuid()
            );
        } catch (MerchantOnboardingStepNotFoundException | WorkflowException $exception) {
            throw new MerchantOnboardingStepTransitionException();
        }

        try {
            $identificationResponse = $this->identityVerificationService->startVerificationCase(
                (new IdentityVerificationStartRequestDTO())
                    ->setFirstName($request->getFirstName())
                    ->setLastName($request->getLastName())
                    ->setEmail($request->getEmail())
                    ->setRedirectUrlCouponRequested($request->getRedirectUrlCouponRequested())
                    ->setRedirectUrlReviewPending($request->getRedirectUrlReviewPending())
                    ->setRedirectUrlDeclined($request->getRedirectUrlDeclined())
            );
        } catch (IdentityVerificationServiceException $exception) {
            throw new StartIdentityVerificationException();
        }

        $this->merchantUserRepository->assignIdentityVerificationCaseToUser(
            $request->getMerchantUserId(),
            $identificationResponse->getUuid()
        );

        return new StartIdentityVerificationResponse($identificationResponse->getUrl());
    }
}
