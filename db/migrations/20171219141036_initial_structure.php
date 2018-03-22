<?php

use Phinx\Migration\AbstractMigration;

class InitialStructure extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('orders')
            ->addColumn('created_at', 'datetime')
            ->create()
        ;
    }
}
