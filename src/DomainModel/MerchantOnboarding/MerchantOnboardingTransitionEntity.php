<?php

namespace App\DomainModel\MerchantOnboarding;

use Billie\PdoBundle\DomainModel\StateTransitionEntity\AbstractStateTransitionEntity;

class MerchantOnboardingTransitionEntity extends AbstractStateTransitionEntity
{
    public const TRANSITION_COMPLETE = 'complete';

    public const TRANSITION_CANCEL = 'cancel';

    public const ALL_TRANSITIONS = [self::TRANSITION_COMPLETE, self::TRANSITION_CANCEL];

    private $merchantOnboardingId;

    public function getMerchantOnboardingId(): int
    {
        return $this->merchantOnboardingId;
    }

    public function setMerchantOnboardingId(int $merchantOnboardingId): MerchantOnboardingTransitionEntity
    {
        $this->merchantOnboardingId = $merchantOnboardingId;

        return $this;
    }

    public function getReferenceId(): int
    {
        return $this->getMerchantOnboardingId();
    }

    public function setReferenceId(int $referenceId): MerchantOnboardingTransitionEntity
    {
        return $this->setMerchantOnboardingId($referenceId);
    }
}
