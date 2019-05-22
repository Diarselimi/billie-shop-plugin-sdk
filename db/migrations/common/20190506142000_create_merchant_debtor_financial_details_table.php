<?php

use Phinx\Migration\AbstractMigration;

class CreateMerchantDebtorFinancialDetailsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('merchant_debtor_financial_details')
            ->addColumn('merchant_debtor_id', 'integer', ['null' => false])
            ->addColumn('financing_limit', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 2])
            ->addColumn('financing_power', 'decimal', ['null' => false, 'precision' => 12, 'scale' => 2])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addForeignKey('merchant_debtor_id', 'merchants_debtors', 'id')
            ->addIndex('merchant_debtor_id', ['unique' => false])
            ->create()
        ;
    }
}
