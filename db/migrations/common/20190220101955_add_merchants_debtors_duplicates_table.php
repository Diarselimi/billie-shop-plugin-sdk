<?php

use Phinx\Migration\AbstractMigration;

class AddMerchantsDebtorsDuplicatesTable extends AbstractMigration
{
    public function change()
    {
        $this->table('merchants_debtors_duplicates')
            ->addColumn('duplicated_merchant_debtor_id', 'integer', ['null' => false])
            ->addColumn('main_merchant_debtor_id', 'integer', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('duplicated_merchant_debtor_id', 'merchants_debtors', 'id')
            ->addForeignKey('main_merchant_debtor_id', 'merchants_debtors', 'id')
            ->addIndex(['duplicated_merchant_debtor_id', 'main_merchant_debtor_id'], ['unique' => true, 'name' => 'unique_duplicate'])
            ->create()
        ;
    }
}
