<?php

namespace App\DomainModel\MerchantOnboarding;

use App\Support\AbstractFactory;

class MerchantOnboardingStepTransitionEntityFactory extends AbstractFactory
{
    public function createFromArray(array $data): MerchantOnboardingStepTransitionEntity
    {
        return (new MerchantOnboardingStepTransitionEntity())
            ->setId($data['id'])
            ->setMerchantOnboardingStepId($data['merchant_onboarding_step_id'])
            ->setTransition($data['transition'])
            ->setFrom($data['from'])
            ->setTo($data['to'])
            ->setTransitedAt(new \DateTime($data['transited_at']));
    }
}
