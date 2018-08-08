<?php

use Phinx\Migration\AbstractMigration;

class MakePersonFieldsOptional extends AbstractMigration
{
    public function change()
    {
        $this->table('persons')
            ->changeColumn('gender', 'char', ['null' => true])
            ->changeColumn('first_name', 'string', ['null' => true])
            ->changeColumn('last_name', 'string', ['null' => true])
            ->update()
        ;
    }
}
