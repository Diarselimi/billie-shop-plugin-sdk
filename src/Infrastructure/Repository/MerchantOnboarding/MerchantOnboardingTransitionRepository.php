<?php

namespace App\Infrastructure\Repository\MerchantOnboarding;

use App\DomainModel\MerchantOnboarding\MerchantOnboardingTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingTransitionRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractStateTransitionRepository;

class MerchantOnboardingTransitionRepository extends AbstractStateTransitionRepository implements MerchantOnboardingTransitionRepositoryInterface
{
    public const TABLE_NAME = 'merchant_onboarding_transitions';

    public function insert(MerchantOnboardingTransitionEntity $entity): void
    {
        $this->insertStateTransition($entity, self::TABLE_NAME, 'merchant_onboarding_id');
    }

    /**
     * @return MerchantOnboardingTransitionEntity[]
     */
    public function findByOnboarding(int $onboardingId): array
    {
        return $this->findByReferenceId(self::TABLE_NAME, 'merchant_onboarding_id', MerchantOnboardingTransitionEntity::class, $onboardingId);
    }
}
