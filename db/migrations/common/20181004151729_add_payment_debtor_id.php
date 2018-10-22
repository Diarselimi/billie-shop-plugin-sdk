<?php


use Phinx\Migration\AbstractMigration;

class AddPaymentDebtorId extends AbstractMigration
{
    public function change()
    {
        $this->table('merchant_debtors')
            ->addColumn('payment_debtor_id', 'string', ['null' => true, 'after' => 'merchant_id'])
            ->addIndex('payment_debtor_id', ['unique' => true])
            ->update()
        ;
    }
}
