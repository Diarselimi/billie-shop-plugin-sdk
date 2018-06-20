<?php

use Phinx\Migration\AbstractMigration;

class AddTransitionColumns extends AbstractMigration
{
    public function change()
    {
        $this->table('order_transitions')
            ->addColumn('to', 'string', ['null' => false, 'after' => 'id'])
            ->addColumn('from', 'string', ['null' => true, 'after' => 'id'])
            ->update()
        ;
    }
}
