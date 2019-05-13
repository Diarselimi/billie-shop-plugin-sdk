<?php

use Phinx\Migration\AbstractMigration;

class AddForgivenAmountInOrdersTable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('orders')
            ->addColumn('amount_forgiven', 'float', ['after' => 'amount_tax', 'null' => true])
            ->update();
    }
}
