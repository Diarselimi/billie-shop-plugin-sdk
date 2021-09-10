<?php

declare(strict_types=1);

use App\Infrastructure\Phinx\TransactionalMigration;

final class AddOrdersStateIndex extends TransactionalMigration
{
    public function migrate(): void
    {
        $this
            ->table('orders')
            ->addIndex(['state'], ['name' => 'idx_orders__state'])
            ->update();
    }
}
