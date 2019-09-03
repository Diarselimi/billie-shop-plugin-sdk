<?php

use Phinx\Migration\AbstractMigration;

class AddIndicesToBoostPerformance extends AbstractMigration
{
    public function change()
    {
        $this->table('debtor_external_data')
            ->addIndex('merchant_external_id')
            ->update()
        ;

        $this->table('merchants_debtors')
            ->addIndex('uuid')
            ->update()
        ;

        $this->table('orders')
            ->addIndex('external_code')
            ->addIndex('invoice_number')
            ->update()
        ;
    }
}
