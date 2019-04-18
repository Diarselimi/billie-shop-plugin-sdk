<?php

use Phinx\Migration\AbstractMigration;

class AddOauthClientIdColumnToMerchantsTable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('merchants')
            ->addColumn('oauth_client_id', 'string', ['null' => true, 'limit' => 36, 'after' => 'api_key'])
            ->addIndex('oauth_client_id', ['unique' => true])
            ->update()
        ;
    }
}
