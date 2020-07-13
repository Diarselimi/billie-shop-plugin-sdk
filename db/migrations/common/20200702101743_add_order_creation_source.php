<?php

use App\Infrastructure\Phinx\TransactionalMigration;
use App\Infrastructure\Repository\OrderRepository;

class AddOrderCreationSource extends TransactionalMigration
{
    public function migrate()
    {
        $this->table(OrderRepository::TABLE_NAME)
            ->addColumn('creation_source', 'string', ['null' => false])
            ->update();

        $this->execute("
            UPDATE orders
            SET creation_source = IF(checkout_session_id IS NULL, 'api', 'checkout')
        ");
    }
}
