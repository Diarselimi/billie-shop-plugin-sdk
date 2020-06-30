<?php

declare(strict_types=1);

namespace App\DomainModel\IdentityVerification;

use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;

class IdentityVerificationSucceeder
{
    private $userRepository;

    private $transitionService;

    public function __construct(
        MerchantUserRepositoryInterface $userRepository,
        MerchantStepTransitionService $transitionService
    ) {
        $this->userRepository = $userRepository;
        $this->transitionService = $transitionService;
    }

    public function succeedIdentifcationVerification(string $caseUuid): void
    {
        $merchantUser = $this->userRepository->getOneByIdentityVerificationCaseUuid($caseUuid);
        if (!$merchantUser) {
            throw new MerchantUserNotFoundException('Merchant user not found');
        }

        $this->transitionService->transition(
            MerchantOnboardingStepEntity::STEP_IDENTITY_VERIFICATION,
            MerchantOnboardingStepTransitionEntity::TRANSITION_COMPLETE,
            $merchantUser->getMerchantId()
        );
    }
}
