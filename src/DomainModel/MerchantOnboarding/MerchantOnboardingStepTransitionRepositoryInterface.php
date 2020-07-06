<?php

namespace App\DomainModel\MerchantOnboarding;

interface MerchantOnboardingStepTransitionRepositoryInterface
{
    public function insert(MerchantOnboardingStepTransitionEntity $entity): void;

    public function findNewestByStepId(int $stepId): ?MerchantOnboardingStepTransitionEntity;
}
