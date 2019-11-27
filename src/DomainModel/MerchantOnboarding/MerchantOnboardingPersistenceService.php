<?php

namespace App\DomainModel\MerchantOnboarding;

class MerchantOnboardingPersistenceService
{
    private $onboardingRepository;

    private $onboardingStepRepository;

    private $onboardingEntityFactory;

    private $onboardingStepEntityFactory;

    private $onboardingContainerFactory;

    public function __construct(
        MerchantOnboardingRepositoryInterface $onboardingRepository,
        MerchantOnboardingStepRepositoryInterface $onboardingStepRepository,
        MerchantOnboardingEntityFactory $onboardingEntityFactory,
        MerchantOnboardingStepEntityFactory $onboardingStepEntityFactory,
        MerchantOnboardingContainerFactory $onboardingContainerFactory
    ) {
        $this->onboardingRepository = $onboardingRepository;
        $this->onboardingStepRepository = $onboardingStepRepository;
        $this->onboardingEntityFactory = $onboardingEntityFactory;
        $this->onboardingStepEntityFactory = $onboardingStepEntityFactory;
        $this->onboardingContainerFactory = $onboardingContainerFactory;
    }

    public function createWithSteps(int $merchantId): MerchantOnboardingContainer
    {
        $onboardingEntity = $this->onboardingEntityFactory->create(MerchantOnboardingEntity::INITIAL_STATE, $merchantId);
        $this->onboardingRepository->insert($onboardingEntity);
        $onboardingSteps = [];

        foreach (MerchantOnboardingStepEntity::ALL_STEPS as $name) {
            $onboardingStep = $this->onboardingStepEntityFactory->create(
                $name,
                MerchantOnboardingStepEntity::INITIAL_STATE,
                $onboardingEntity->getId()
            )->setIsInternal(in_array($name, MerchantOnboardingStepEntity::ALL_INTERNAL_STEPS, true));
            $this->onboardingStepRepository->insert($onboardingStep);
            $onboardingSteps[] = $onboardingStep;
        }

        return $this->onboardingContainerFactory->createWithData($merchantId, $onboardingEntity, ...$onboardingSteps);
    }
}
