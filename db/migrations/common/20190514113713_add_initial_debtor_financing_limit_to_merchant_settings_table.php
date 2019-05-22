<?php

use Phinx\Migration\AbstractMigration;

class AddInitialDebtorFinancingLimitToMerchantSettingsTable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('merchant_settings')
            ->addColumn(
                'initial_debtor_financing_limit',
                'decimal',
                ['null' => true, 'precision' => 12, 'scale' => 2, 'after' => 'merchant_id']
            )
            ->update()
        ;

        $this->execute('UPDATE merchant_settings SET initial_debtor_financing_limit = debtor_financing_limit');

        $this
            ->table('merchant_settings')
            ->changeColumn(
                'initial_debtor_financing_limit',
                'decimal',
                ['null' => false, 'precision' => 12, 'scale' => 2, 'after' => 'merchant_id']
            )
            ->update()
        ;
    }
}
