<?php

namespace App\DomainModel\MerchantOnboarding;

interface MerchantOnboardingStepRepositoryInterface
{
    public function getOneByStepNameAndPaymentUuid(string $name, int $merchantId): ?MerchantOnboardingStepEntity;

    public function getOneByStepNameAndMerchant(string $name, int $merchantId): ?MerchantOnboardingStepEntity;

    /**
     * @return MerchantOnboardingStepEntity[]
     */
    public function findByMerchantOnboardingId(int $merchantOnboardingId, bool $includeInternalSteps): array;

    public function insert(MerchantOnboardingStepEntity $entity): void;

    public function update(MerchantOnboardingStepEntity $entity): void;

    public function findByOnboardingStepAndMerchant(string $step, int $merchantId): ?MerchantOnboardingStepEntity;
}
