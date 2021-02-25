<?php

use App\Infrastructure\Phinx\TransactionalMigration;

class UpdateUnshippedAmountsForAllFinancialDetails extends TransactionalMigration
{
    protected function migrate()
    {
        $this->execute("
           UPDATE `order_financial_details` as fd INNER JOIN orders o ON fd.`order_id` = o.id SET `unshipped_amount_gross` = `amount_gross`, `unshipped_amount_net` = `amount_net`, `unshipped_amount_tax` = `amount_tax` WHERE o.shipped_at is null;
        ");

        $this->execute("
           UPDATE `order_financial_details` as fd INNER JOIN orders o ON fd.`order_id` = o.id SET `unshipped_amount_gross` = 0.00, `unshipped_amount_net` = 0.00, `unshipped_amount_tax` = 0.00 WHERE o.shipped_at is not null;
        ");
    }
}
