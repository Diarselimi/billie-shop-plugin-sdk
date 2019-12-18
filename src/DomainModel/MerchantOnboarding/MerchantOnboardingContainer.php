<?php

namespace App\DomainModel\MerchantOnboarding;

class MerchantOnboardingContainer
{
    private $merchantId;

    private $onboardingRepository;

    private $onboardingTransitionRepository;

    private $onboardingStepRepository;

    private $onboardingEntity;

    private $onboardingTransitions;

    private $onboardingSteps;

    public function __construct(
        int $merchantId,
        MerchantOnboardingRepositoryInterface $onboardingRepository,
        MerchantOnboardingTransitionRepositoryInterface $onboardingTransitionRepository,
        MerchantOnboardingStepRepositoryInterface $onboardingStepRepository
    ) {
        $this->merchantId = $merchantId;
        $this->onboardingRepository = $onboardingRepository;
        $this->onboardingTransitionRepository = $onboardingTransitionRepository;
        $this->onboardingStepRepository = $onboardingStepRepository;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getOnboarding(): MerchantOnboardingEntity
    {
        if ($this->onboardingEntity) {
            return $this->onboardingEntity;
        }

        $this->onboardingEntity = $this->onboardingRepository->findNewestByMerchant($this->merchantId);

        if (!$this->onboardingEntity) {
            throw new \RuntimeException("Merchant {$this->merchantId} has no onboarding data.");
        }

        return $this->onboardingEntity;
    }

    /**
     * @return MerchantOnboardingTransitionEntity[]
     */
    public function getOnboardingTransitions(): array
    {
        if (is_array($this->onboardingTransitions)) {
            return $this->onboardingTransitions;
        }

        $this->onboardingTransitions = $this->onboardingTransitionRepository->findByOnboarding(
            $this->getOnboarding()->getId()
        );

        return $this->onboardingTransitions;
    }

    /**
     * @return MerchantOnboardingStepEntity[]
     */
    public function getOnboardingSteps(): array
    {
        if (is_array($this->onboardingSteps)) {
            return $this->onboardingSteps;
        }

        $this->onboardingSteps = $this->onboardingStepRepository->findByMerchantOnboardingId(
            $this->getOnboarding()->getId(),
            false
        );

        return $this->onboardingSteps;
    }

    public function getOnboardingStep(string $name): MerchantOnboardingStepEntity
    {
        foreach ($this->getOnboardingSteps() as $step) {
            if ($step->getName() === $name) {
                return $step;
            }
        }

        throw new MerchantOnboardingStepNotFoundException("Onboarding Step {$name} not found for merchant {$this->merchantId}");
    }

    public function setOnboarding(MerchantOnboardingEntity $onboardingEntity): MerchantOnboardingContainer
    {
        $this->onboardingEntity = $onboardingEntity;

        return $this;
    }

    public function setOnboardingSteps(MerchantOnboardingStepEntity ... $onboardingStepEntities): MerchantOnboardingContainer
    {
        $this->onboardingSteps = $onboardingStepEntities;

        return $this;
    }
}
