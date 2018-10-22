<?php

use Phinx\Migration\AbstractMigration;

class AddPaymentMerchantIdColumnToMerchants extends AbstractMigration
{
    public function change()
    {
        $this->table('merchants')
            ->addColumn('payment_merchant_id', 'string', ['null' => true, 'after' => 'company_id'])
            ->addIndex('payment_merchant_id', ['unique' => true])
            ->update();
    }
}
