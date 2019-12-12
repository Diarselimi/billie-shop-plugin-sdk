<?php

namespace App\Application\UseCase\MerchantFinishIntegration;

use App\Application\UseCase\ValidatedRequestInterface;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;

class MerchantFinishIntegrationRequest implements ValidatedRequestInterface
{
    private $merchantPaymentUuid;

    private $stepName;

    private $transitionName;

    public function __construct(string $merchantPaymentUuid)
    {
        $this->merchantPaymentUuid = $merchantPaymentUuid;
        $this->stepName = MerchantOnboardingStepEntity::STEP_TECHNICAL_INTEGRATION;
        $this->transitionName = MerchantOnboardingStepTransitionEntity::TRANSITION_REQUEST_CONFIRMATION;
    }

    public function getStepName(): string
    {
        return $this->stepName;
    }

    public function getTransitionName(): string
    {
        return $this->transitionName;
    }

    public function getMerchantPaymentUuid(): string
    {
        return $this->merchantPaymentUuid;
    }
}
