<?php

declare(strict_types=1);

namespace App\Application\UseCase\StartIdentityVerification;

use App\Application\Exception\MerchantOnboardingStepTransitionException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\StartIdentityVerificationResponse;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\IdentityVerification\IdentityVerificationServiceException;
use App\DomainModel\IdentityVerification\IdentityVerificationServiceInterface;
use App\DomainModel\IdentityVerification\IdentityVerificationStartRequestDTO;
use App\DomainModel\IdentityVerification\StartIdentityVerificationGuard;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepNotFoundException;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepRepositoryInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;

class StartIdentityVerificationUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $identityVerificationService;

    private $merchantUserRepository;

    private $stepTransitionService;

    private $merchantOnboardingStepRepository;

    private $startIdentityVerificationGuard;

    public function __construct(
        IdentityVerificationServiceInterface $identityVerificationService,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantStepTransitionService $stepTransitionService,
        MerchantOnboardingStepRepositoryInterface $merchantOnboardingStepRepository,
        StartIdentityVerificationGuard $startIdentityVerificationGuard
    ) {
        $this->identityVerificationService = $identityVerificationService;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->stepTransitionService = $stepTransitionService;
        $this->merchantOnboardingStepRepository = $merchantOnboardingStepRepository;
        $this->startIdentityVerificationGuard = $startIdentityVerificationGuard;
    }

    public function execute(StartIdentityVerificationRequest $request): StartIdentityVerificationResponse
    {
        $this->validateRequest($request);
        $step = $this->merchantOnboardingStepRepository->getOneByStepNameAndMerchant(
            MerchantOnboardingStepEntity::STEP_IDENTITY_VERIFICATION,
            $request->getMerchantId()
        );
        if (!$step) {
            throw new MerchantOnboardingStepNotFoundException();
        }

        switch ($step->getState()) {
            case MerchantOnboardingStepEntity::STATE_NEW:
                try {
                    $this->stepTransitionService->transitionStepEntity(
                        $step,
                        MerchantOnboardingStepTransitionEntity::TRANSITION_REQUEST_CONFIRMATION,
                        $request->getMerchantId()
                    );
                } catch (WorkflowException $exception) {
                    throw new MerchantOnboardingStepTransitionException();
                }

                break;
            case MerchantOnboardingStepEntity::STATE_PENDING:
                if (!$this->startIdentityVerificationGuard->startIdentityVerificationAllowed()) {
                    throw new StartIdentityVerificationException('There is a valid case already');
                }

                break;
            case MerchantOnboardingStepEntity::STATE_COMPLETE:
                throw new MerchantOnboardingStepTransitionException();
        }

        $identificationRequest = $this->buildIdentityVerificationStartRequestDTO($request);

        try {
            $identificationResponse = $this->identityVerificationService->startVerificationCase($identificationRequest);
        } catch (IdentityVerificationServiceException $exception) {
            throw new StartIdentityVerificationException();
        }

        return new StartIdentityVerificationResponse($identificationResponse->getUrl());
    }

    private function buildIdentityVerificationStartRequestDTO(StartIdentityVerificationRequest $request): IdentityVerificationStartRequestDTO
    {
        $identificationRequest = (new IdentityVerificationStartRequestDTO())
            ->setMerchantUserId($request->getMerchantUserId())
            ->setFirstName($request->getFirstName())
            ->setLastName($request->getLastName())
            ->setEmail($request->getEmail())
            ->setRedirectUrlCouponRequested($request->getRedirectUrlCouponRequested())
            ->setRedirectUrlReviewPending($request->getRedirectUrlReviewPending())
            ->setRedirectUrlDeclined($request->getRedirectUrlDeclined());

        if ($request->getSignatoryPowerUuid()) {
            $identificationRequest->setSignatoryPowerUuid($request->getSignatoryPowerUuid());
        }

        return $identificationRequest;
    }
}
