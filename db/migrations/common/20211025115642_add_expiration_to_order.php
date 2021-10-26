<?php

use App\Infrastructure\Phinx\TransactionalMigration;

final class AddExpirationToOrder extends TransactionalMigration
{
    public function migrate(): void
    {
        $this
            ->table('orders')
            ->addColumn('expiration', 'datetime', ['null' => true, 'after' => 'workflow_name'])
            ->update();
    }
}
