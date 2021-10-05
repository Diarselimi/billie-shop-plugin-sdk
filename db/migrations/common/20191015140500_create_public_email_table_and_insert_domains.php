<?php

use Phinx\Migration\AbstractMigration;

class CreatePublicEmailTableAndInsertDomains extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('public_domains')
            ->addColumn('domain', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addIndex('domain')
            ->save()
        ;
    }
}
