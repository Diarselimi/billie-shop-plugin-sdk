<?php

use Phinx\Migration\AbstractMigration;

class MakePersonPhoneOptional extends AbstractMigration
{
    public function change()
    {
        $this->table('persons')
            ->changeColumn('phone', 'string', ['null' => true])
            ->update()
        ;
    }
}
