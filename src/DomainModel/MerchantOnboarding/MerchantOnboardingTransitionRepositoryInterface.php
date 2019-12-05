<?php

namespace App\DomainModel\MerchantOnboarding;

interface MerchantOnboardingTransitionRepositoryInterface
{
    public function insert(MerchantOnboardingTransitionEntity $entity): void;

    /**
     * @return MerchantOnboardingTransitionEntity[]
     */
    public function findByOnboarding(int $onboardingId): array;
}
