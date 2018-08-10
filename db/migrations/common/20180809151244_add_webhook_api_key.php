<?php

use Phinx\Migration\AbstractMigration;

class AddWebhookApiKey extends AbstractMigration
{
    public function change()
    {
        $this->table('merchants')
            ->addColumn('webhook_authorization', 'string', ['null' => true, 'after' => 'webhook_url'])
            ->update()
        ;
    }
}
