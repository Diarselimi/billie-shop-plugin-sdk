<?php

namespace App\DomainModel\MerchantOnboarding;

interface MerchantOnboardingStepRepositoryInterface
{
    public function getOneByNameAndMerchant(string $name, string $merchantPaymentUuid): ?MerchantOnboardingStepEntity;

    /**
     * @param  int                            $merchantOnboardingId
     * @return MerchantOnboardingStepEntity[]
     */
    public function findByMerchantOnboardingId(int $merchantOnboardingId): array;

    public function insert(MerchantOnboardingStepEntity $entity): void;

    public function update(MerchantOnboardingStepEntity $entity): void;
}
