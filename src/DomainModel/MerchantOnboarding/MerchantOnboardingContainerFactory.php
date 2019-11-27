<?php

namespace App\DomainModel\MerchantOnboarding;

class MerchantOnboardingContainerFactory
{
    private $onboardingRepository;

    private $onboardingStepRepository;

    public function __construct(
        MerchantOnboardingRepositoryInterface $onboardingRepository,
        MerchantOnboardingStepRepositoryInterface $onboardingStepRepository
    ) {
        $this->onboardingRepository = $onboardingRepository;
        $this->onboardingStepRepository = $onboardingStepRepository;
    }

    public function create(int $merchantId): MerchantOnboardingContainer
    {
        return new MerchantOnboardingContainer($merchantId, $this->onboardingRepository, $this->onboardingStepRepository);
    }

    public function createWithData(
        int $merchantId,
        MerchantOnboardingEntity $onboardingEntity,
        MerchantOnboardingStepEntity ... $onboardingStepEntities
    ): MerchantOnboardingContainer {
        return $this->create($merchantId)->setOnboarding($onboardingEntity)->setOnboardingSteps(... $onboardingStepEntities);
    }
}
