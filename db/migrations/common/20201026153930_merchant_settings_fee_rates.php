<?php

use App\Infrastructure\Phinx\TransactionalMigration;

class MerchantSettingsFeeRates extends TransactionalMigration
{
    public function migrate()
    {
        $this->table('merchant_settings')
            ->addColumn('fee_rates', 'json', ['null' => true, 'after' => 'min_order_amount'])
            ->update();
    }
}
