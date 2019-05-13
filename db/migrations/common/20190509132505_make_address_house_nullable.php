<?php

use Phinx\Migration\AbstractMigration;

class MakeAddressHouseNullable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('addresses')
            ->changeColumn('house', 'string', ['null' => true])
            ->update()
        ;
    }
}
