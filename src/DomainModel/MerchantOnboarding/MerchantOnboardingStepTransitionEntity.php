<?php

namespace App\DomainModel\MerchantOnboarding;

use Billie\PdoBundle\DomainModel\StateTransitionEntity\AbstractStateTransitionEntity;

class MerchantOnboardingStepTransitionEntity extends AbstractStateTransitionEntity
{
    private $merchantOnboardingStepId;

    public function getMerchantOnboardingStepId(): int
    {
        return $this->merchantOnboardingStepId;
    }

    public function setMerchantOnboardingStepId(int $merchantOnboardingStepId): MerchantOnboardingStepTransitionEntity
    {
        $this->merchantOnboardingStepId = $merchantOnboardingStepId;

        return $this;
    }

    public function getReferenceId(): int
    {
        return $this->getMerchantOnboardingStepId();
    }

    public function setReferenceId(int $referenceId): MerchantOnboardingStepTransitionEntity
    {
        return $this->setMerchantOnboardingStepId($referenceId);
    }
}
