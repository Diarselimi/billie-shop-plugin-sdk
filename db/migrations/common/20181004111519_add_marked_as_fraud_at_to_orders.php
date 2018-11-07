<?php

use Phinx\Migration\AbstractMigration;

class AddMarkedAsFraudAtToOrders extends AbstractMigration
{
    public function change()
    {
        $this->table('orders')
            ->addColumn('marked_as_fraud_at', 'datetime', ['null' => true, 'default' => null, 'after' => 'shipped_at'])
            ->update();
    }
}
