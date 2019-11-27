<?php

namespace App\Infrastructure\Repository\MerchantOnboarding;

use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractStateTransitionRepository;

class MerchantOnboardingStepTransitionRepository extends AbstractStateTransitionRepository implements MerchantOnboardingStepTransitionRepositoryInterface
{
    public const TABLE_NAME = 'merchant_onboarding_step_transitions';

    public function insert(MerchantOnboardingStepTransitionEntity $entity): void
    {
        $this->insertStateTransition($entity, self::TABLE_NAME, 'merchant_onboarding_step_id');
    }
}
