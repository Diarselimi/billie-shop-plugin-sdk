<?php

use Phinx\Migration\AbstractMigration;

class MakeOrderExternalCodeNullable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('orders')
            ->changeColumn('external_code', 'string', ['null' => true])
            ->update()
        ;
    }
}
