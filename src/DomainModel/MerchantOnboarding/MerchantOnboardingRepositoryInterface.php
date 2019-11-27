<?php

namespace App\DomainModel\MerchantOnboarding;

interface MerchantOnboardingRepositoryInterface
{
    public function insert(MerchantOnboardingEntity $entity): void;

    public function findNewestByMerchant(int $merchantId): ?MerchantOnboardingEntity;
}
