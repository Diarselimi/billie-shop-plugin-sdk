<?php

use Phinx\Migration\AbstractMigration;

class SetInitialTestMerchantSettings extends AbstractMigration
{
    public function up()
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $this->table('merchant_settings')->insert([
            'merchant_id' => 1,
            'debtor_financing_limit' => 7500,
            'min_order_amount' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ])->saveData()
        ;
    }
}
