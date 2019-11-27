<?php

namespace App\DomainModel\MerchantOnboarding;

class MerchantOnboardingContainer
{
    private $merchantId;

    private $onboardingRepository;

    private $onboardingStepRepository;

    private $onboardingEntity;

    private $onboardingStepsEntities;

    public function __construct(
        int $merchantId,
        MerchantOnboardingRepositoryInterface $onboardingRepository,
        MerchantOnboardingStepRepositoryInterface $onboardingStepRepository
    ) {
        $this->merchantId = $merchantId;
        $this->onboardingRepository = $onboardingRepository;
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
     * @return MerchantOnboardingStepEntity[]
     */
    public function getOnboardingSteps(): array
    {
        if (is_array($this->onboardingStepsEntities)) {
            return $this->onboardingStepsEntities;
        }

        $this->onboardingStepsEntities = $this->onboardingStepRepository->findByMerchantOnboardingId(
            $this->getOnboarding()->getId()
        );

        return $this->onboardingStepsEntities;
    }

    public function setOnboarding(MerchantOnboardingEntity $onboardingEntity): MerchantOnboardingContainer
    {
        $this->onboardingEntity = $onboardingEntity;

        return $this;
    }

    public function setOnboardingSteps(MerchantOnboardingStepEntity ... $onboardingStepEntities): MerchantOnboardingContainer
    {
        $this->onboardingStepsEntities = $onboardingStepEntities;

        return $this;
    }
}
