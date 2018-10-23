<?php

use Phinx\Migration\AbstractMigration;

class RemoveMerchantExternalId extends AbstractMigration
{
    public function change()
    {
        $this->table('merchants_debtors')
            ->removeColumn('external_id')
            ->update()
        ;

        $this->table('merchants_debtors')
            ->addIndex(['merchant_id', 'debtor_id'], ['unique' => true])
            ->update()
        ;
        
        $this->table('debtor_external_data')
            ->addColumn('merchant_external_id', 'string', ['null' => true, 'after' => 'is_established_customer'])
            ->update()
        ;
    }
}
