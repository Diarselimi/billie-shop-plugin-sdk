<?php

use Phinx\Migration\AbstractMigration;

class AddWebhookUrl extends AbstractMigration
{
    public function change()
    {
        $this->table('merchants')
            ->addColumn('webhook_url', 'string', ['null' => true, 'after' => 'company_id'])
            ->update();
    }
}
