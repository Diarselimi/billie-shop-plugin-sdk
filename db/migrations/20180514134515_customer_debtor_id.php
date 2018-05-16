<?php

use Phinx\Migration\AbstractMigration;

class CustomerDebtorId extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('customers')
            ->addColumn('debtor_id', 'string', ['null' => false, 'after' => 'name'])
            ->update()
        ;
    }
}
