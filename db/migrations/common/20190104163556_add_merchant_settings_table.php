<?php

use Phinx\Migration\AbstractMigration;

class AddMerchantSettingsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('merchant_settings')
            ->addColumn('merchant_id', 'integer', ['null' => false])
            ->addColumn('debtor_financing_limit', 'float', ['null' => false])
            ->addColumn('min_order_amount', 'float', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex('merchant_id', ['unique' => true])
            ->addForeignKey('merchant_id', 'merchants', 'id')
            ->create()
        ;
    }
}
