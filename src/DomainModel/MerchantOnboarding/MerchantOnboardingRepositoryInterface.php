<?php

namespace App\DomainModel\MerchantOnboarding;

interface MerchantOnboardingRepositoryInterface
{
    public function insert(MerchantOnboardingEntity $entity): void;

    public function findNewestByMerchant(int $merchantId): ?MerchantOnboardingEntity;

    public function findNewestByPaymentUuid(string $paymentUuid): ?MerchantOnboardingEntity;

    public function update(MerchantOnboardingEntity $entity): void;
}
