<?php

use App\Infrastructure\Phinx\TransactionalMigration;

class AddOrderWorkflow extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table('orders')
            ->addColumn('workflow_name', 'string', ['null' => true, 'after' => 'checkout_session_id'])
            ->update()
        ;

        $this->execute("
            UPDATE orders
            SET workflow_name = 'order_v1'
        ");

        $this->table('orders')
            ->changeColumn('workflow_name', 'string', ['null' => false])
            ->save()
        ;
    }
}
