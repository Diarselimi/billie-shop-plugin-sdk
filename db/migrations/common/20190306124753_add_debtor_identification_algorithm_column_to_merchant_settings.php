<?php

use Phinx\Migration\AbstractMigration;

class AddDebtorIdentificationAlgorithmColumnToMerchantSettings extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('merchant_settings')
            ->addColumn(
                'debtor_identification_algorithm',
                'string',
                [
                    'null' => false,
                    'after' => 'score_thresholds_configuration_id',
                    'default' => '0',
                ]
            )
            ->update()
        ;
    }
}
