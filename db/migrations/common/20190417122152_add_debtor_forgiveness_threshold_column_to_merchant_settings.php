<?php

use App\DomainModel\MerchantSettings\MerchantSettingsEntity;
use App\Infrastructure\Repository\MerchantSettingsRepository;
use Phinx\Migration\AbstractMigration;

class AddDebtorForgivenessThresholdColumnToMerchantSettings extends AbstractMigration
{
    public function change()
    {
        $table = MerchantSettingsRepository::TABLE_NAME;
        $column = 'debtor_forgiveness_threshold';

        $this
            ->table($table)
            ->addColumn($column, 'float', [
                'null' => false,
                'default' => MerchantSettingsEntity::DEFAULT_DEBTOR_FORGIVENESS_THRESHOLD,
                'after' => 'use_experimental_identification',
            ])
            ->update()
        ;
    }
}
