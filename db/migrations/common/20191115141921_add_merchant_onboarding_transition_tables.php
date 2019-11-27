<?php

use App\Infrastructure\Phinx\MigrationHelperTrait;
use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantOnboarding\MerchantOnboardingStepTransitionRepository;
use App\Infrastructure\Repository\MerchantOnboarding\MerchantOnboardingTransitionRepository;

class AddMerchantOnboardingTransitionTables extends TransactionalMigration
{
    use MigrationHelperTrait;

    public function migrate()
    {
        $this->setupStateTransitionTableCreation(
            $this->table(MerchantOnboardingTransitionRepository::TABLE_NAME),
            'merchant_onboarding_id',
            'merchant_onboardings'
        )->create();

        $this->setupStateTransitionTableCreation(
            $this->table(MerchantOnboardingStepTransitionRepository::TABLE_NAME),
            'merchant_onboarding_step_id',
            'merchant_onboarding_steps'
        )->create();
    }
}
