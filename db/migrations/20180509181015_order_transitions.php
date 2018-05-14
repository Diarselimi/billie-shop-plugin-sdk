<?php

use Phinx\Migration\AbstractMigration;

class OrderTransitions extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('order_transitions')
            ->addColumn('transition', 'string', ['null' => false])
            ->addColumn('order_id', 'integer', ['null' => false])
            ->addColumn('transited_at', 'datetime', ['null' => false])
            ->addForeignKey('order_id', 'orders', 'id')
            ->create()
        ;
    }
}
