<?php

use App\Infrastructure\Phinx\TransactionalMigration;

class AddOrderInvoicesV2 extends TransactionalMigration
{
    protected function migrate()
    {
        $this
            ->table('order_invoices_v2')
            ->addColumn('order_id', 'integer', ['null' => false])
            ->addColumn('invoice_uuid', 'string', ['limit' => 36, 'null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addForeignKey('order_id', 'orders', 'id')
            ->addIndex('order_id')
            ->create()
        ;
    }
}
