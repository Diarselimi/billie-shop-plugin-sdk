<?php

use Phinx\Migration\AbstractMigration;

class DropRolesColumnFromMerchantsTable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('merchants')
            ->removeColumn('roles')
            ->save()
        ;
    }
}
