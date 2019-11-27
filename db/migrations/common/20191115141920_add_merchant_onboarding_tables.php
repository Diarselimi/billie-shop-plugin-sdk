<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\MerchantOnboarding\MerchantOnboardingRepository;
use App\Infrastructure\Repository\MerchantOnboarding\MerchantOnboardingStepRepository;
use App\Infrastructure\Repository\MerchantOnboarding\MerchantOnboardingTransitionRepository;

class AddMerchantOnboardingTables extends TransactionalMigration
{
    public function migrate()
    {
        $this->table(MerchantOnboardingRepository::TABLE_NAME)
            ->addColumn('uuid', 'string', ['null' => false, 'limit' => 36])
            ->addColumn('merchant_id', 'integer', ['null' => false])
            ->addColumn('state', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('merchant_id', 'merchants', 'id')
            ->addIndex(['uuid'], ['unique' => true])
            ->create();

        $this->table(MerchantOnboardingStepRepository::TABLE_NAME)
            ->addColumn('uuid', 'string', ['null' => false, 'limit' => 36])
            ->addColumn('merchant_onboarding_id', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('state', 'string', ['null' => false])
            ->addColumn('is_internal', 'boolean', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex(['uuid'], ['unique' => true])
            ->addForeignKey('merchant_onboarding_id', 'merchant_onboardings', 'id')
            ->create();
    }
}
