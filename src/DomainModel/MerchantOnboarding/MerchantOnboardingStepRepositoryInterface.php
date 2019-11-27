<?php

namespace App\DomainModel\MerchantOnboarding;

interface MerchantOnboardingStepRepositoryInterface
{
    public function insert(MerchantOnboardingStepEntity $entity): void;

    /**
     * @param  int                            $merchantOnboardingId
     * @return MerchantOnboardingStepEntity[]
     */
    public function findByMerchantOnboardingId(int $merchantOnboardingId): array;
}
