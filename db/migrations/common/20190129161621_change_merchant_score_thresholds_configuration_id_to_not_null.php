<?php

use Phinx\Migration\AbstractMigration;

class ChangeMerchantScoreThresholdsConfigurationIdToNotNull extends AbstractMigration
{
    public function change()
    {
        $this->table('merchant_settings')
            ->changeColumn('score_thresholds_configuration_id', 'integer', ['null' => false])
            ->addForeignKey('score_thresholds_configuration_id', 'score_thresholds_configuration')
            ->update()
        ;
    }
}
