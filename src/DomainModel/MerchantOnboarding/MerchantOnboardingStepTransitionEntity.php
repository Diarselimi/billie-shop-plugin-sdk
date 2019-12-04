<?php

namespace App\DomainModel\MerchantOnboarding;

use Billie\PdoBundle\DomainModel\StateTransitionEntity\AbstractStateTransitionEntity;

class MerchantOnboardingStepTransitionEntity extends AbstractStateTransitionEntity
{
    public const TRANSITION_REQUEST_CONFIRMATION = 'request_confirmation';

    public const TRANSITION_COMPLETE = 'complete';

    public const TRANSITION_CANCEL = 'cancel';

    public const ALL_TRANSITIONS = [self::TRANSITION_REQUEST_CONFIRMATION, self::TRANSITION_COMPLETE, self::TRANSITION_CANCEL];

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
