<?php

namespace App\Infrastructure\Repository\MerchantOnboarding;

use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntity;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionEntityFactory;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepTransitionRepositoryInterface;
use Billie\PdoBundle\DomainModel\StateTransitionEntity\StateTransitionEntityFactory;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractStateTransitionRepository;

class MerchantOnboardingStepTransitionRepository extends AbstractStateTransitionRepository implements MerchantOnboardingStepTransitionRepositoryInterface
{
    public const TABLE_NAME = 'merchant_onboarding_step_transitions';

    private $factory;

    public function __construct(
        StateTransitionEntityFactory $stateTransitionEntityFactory,
        MerchantOnboardingStepTransitionEntityFactory $onboardingStepTransitionEntityFactory
    ) {
        parent::__construct($stateTransitionEntityFactory);
        $this->factory = $onboardingStepTransitionEntityFactory;
    }

    public function insert(MerchantOnboardingStepTransitionEntity $entity): void
    {
        $this->insertStateTransition($entity, self::TABLE_NAME, 'merchant_onboarding_step_id');
    }

    public function findNewestByStepId(int $stepId): ?MerchantOnboardingStepTransitionEntity
    {
        $selectFields = array_merge(
            self::SELECT_FIELDS,
            ['merchant_onboarding_step_id']
        );
        $query = $this->generateSelectQuery(self::TABLE_NAME, $selectFields) . ' ' .
            "WHERE merchant_onboarding_step_id = :merchant_onboarding_step_id ORDER BY id DESC LIMIT 1";
        $params = ['merchant_onboarding_step_id' => $stepId];
        $row = $this->doFetchOne($query, $params);

        return $row ? $this->factory->createFromArray($row) : null;
    }
}
