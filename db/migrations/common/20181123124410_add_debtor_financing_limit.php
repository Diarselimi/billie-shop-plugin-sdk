<?php

use Phinx\Migration\AbstractMigration;

class AddDebtorFinancingLimit extends AbstractMigration
{
    public function change()
    {
        $this->table('merchants_debtors')
            ->addColumn('financing_limit', 'float', ['null' => false, 'after' => 'payment_debtor_id', 'default' => 7500])
            ->update();

        $this->table('orders')
            ->addIndex('payment_id', ['unique' => true])
            ->update()
        ;
    }
}
