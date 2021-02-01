<?php

declare(strict_types=1);

use App\Infrastructure\Phinx\TransactionalMigration;

final class AddExtendedDurationColumnToOrders extends TransactionalMigration
{
    protected function migrate()
    {
        $this->table('orders')
            ->addColumn('duration_extension', 'integer', ['null' => true, 'after' => 'workflow_name'])
            ->update()
        ;
    }
}
