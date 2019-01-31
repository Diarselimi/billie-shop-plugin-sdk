<?php

use Phinx\Migration\AbstractMigration;

class AddScoreThresholdsConfigurationTable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('score_thresholds_configuration')
            ->addColumn('crefo_low_score_threshold', 'integer', ['null' => false])
            ->addColumn('crefo_high_score_threshold', 'integer', ['null' => false])
            ->addColumn('schufa_low_score_threshold', 'integer', ['null' => false])
            ->addColumn('schufa_average_score_threshold', 'integer', ['null' => false])
            ->addColumn('schufa_high_score_threshold', 'integer', ['null' => false])
            ->addColumn('schufa_sole_trader_score_threshold', 'integer', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->create()
        ;

        $this->table('merchant_settings')
             ->addColumn('score_thresholds_configuration_id', 'integer', ['null' => true, 'after' => 'min_order_amount'])
            ->addForeignKey('score_thresholds_configuration_id', 'score_thresholds_configuration')
             ->update()
        ;

        $this->table('merchants_debtors')
             ->addColumn('score_thresholds_configuration_id', 'integer', ['null' => true, 'after' => 'financing_limit'])
            ->addForeignKey('score_thresholds_configuration_id', 'score_thresholds_configuration')
             ->update()
        ;
    }
}
