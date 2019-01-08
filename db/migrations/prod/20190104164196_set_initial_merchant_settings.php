<?php

use Phinx\Migration\AbstractMigration;

class SetInitialMerchantSettings extends AbstractMigration
{
    public function up()
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $sql = <<<SQL
INSERT INTO merchant_settings (merchant_id, debtor_financing_limit, min_order_amount, created_at, updated_at)
SELECT merchants.id as merchant_id, 
       7500 as debtor_financing_limit,
       0 as min_order_amount,
       '{$now}' as created_at,
       '{$now}' as updated_at
FROM merchants WHERE merchants.id NOT IN (SELECT merchant_id FROM merchant_settings);
SQL;

        $this->execute($sql);
    }
}
