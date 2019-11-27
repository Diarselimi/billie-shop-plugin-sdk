<?php

namespace App\DomainModel\MerchantOnboarding;

use Billie\PdoBundle\DomainModel\StateTransitionEntity\AbstractStateTransitionEntity;

class MerchantOnboardingTransitionEntity extends AbstractStateTransitionEntity
{
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
