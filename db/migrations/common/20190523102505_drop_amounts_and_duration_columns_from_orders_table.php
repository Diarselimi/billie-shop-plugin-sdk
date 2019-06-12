<?php

use Phinx\Migration\AbstractMigration;

class DropAmountsAndDurationColumnsFromOrdersTable extends AbstractMigration
{
    public function change()
    {
        $this->execute('
            INSERT INTO order_financial_details(order_id, amount_gross, amount_net, amount_tax, duration, created_at, updated_at)
            SELECT id AS order_id, amount_gross, amount_net, amount_tax, duration, created_at, updated_at FROM orders
        ');

        $this->execute('
            ALTER TABLE orders DROP COLUMN amount_gross, DROP COLUMN amount_net, DROP COLUMN amount_tax, DROP COLUMN duration
        ');
    }
}
