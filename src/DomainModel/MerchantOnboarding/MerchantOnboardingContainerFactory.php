<?php

namespace App\DomainModel\MerchantOnboarding;

class MerchantOnboardingContainerFactory
{
    private $onboardingRepository;

    private $onboardingTransitionRepository;

    private $onboardingStepRepository;

    public function __construct(
        MerchantOnboardingRepositoryInterface $onboardingRepository,
        MerchantOnboardingTransitionRepositoryInterface $onboardingTransitionRepository,
        MerchantOnboardingStepRepositoryInterface $onboardingStepRepository
    ) {
        $this->onboardingRepository = $onboardingRepository;
        $this->onboardingTransitionRepository = $onboardingTransitionRepository;
        $this->onboardingStepRepository = $onboardingStepRepository;
    }

    public function create(int $merchantId): MerchantOnboardingContainer
    {
        return new MerchantOnboardingContainer(
            $merchantId,
            $this->onboardingRepository,
            $this->onboardingTransitionRepository,
            $this->onboardingStepRepository
        );
    }

    public function createWithData(
        int $merchantId,
        MerchantOnboardingEntity $onboardingEntity,
        MerchantOnboardingStepEntity ... $onboardingStepEntities
    ): MerchantOnboardingContainer {
        return $this->create($merchantId)->setOnboarding($onboardingEntity)->setOnboardingSteps(... $onboardingStepEntities);
    }
}
