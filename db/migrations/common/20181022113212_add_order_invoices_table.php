<?php

use Phinx\Migration\AbstractMigration;

class AddOrderInvoicesTable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('order_invoices')
            ->addColumn('order_id', 'integer', ['null' => false])
            ->addColumn('file_id', 'integer', ['null' => false])
            ->addColumn('invoice_number', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addForeignKey('order_id', 'orders', 'id')
            ->addIndex('order_id')
            ->create()
        ;
    }
}
