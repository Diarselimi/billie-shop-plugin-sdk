<?php

use Phinx\Migration\AbstractMigration;

class AddShippedAtToOrders extends AbstractMigration
{
    public function change()
    {
        $this->table('orders')
            ->addColumn('shipped_at', 'datetime', ['null' => true, 'after' => 'uuid'])
            ->update();
    }
}
