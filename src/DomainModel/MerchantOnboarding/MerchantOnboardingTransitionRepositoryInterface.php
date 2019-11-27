<?php

namespace App\DomainModel\MerchantOnboarding;

interface MerchantOnboardingTransitionRepositoryInterface
{
    public function insert(MerchantOnboardingTransitionEntity $entity): void;
}
