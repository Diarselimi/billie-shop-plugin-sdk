<?php

use Phinx\Migration\AbstractMigration;

class TestMerchant extends AbstractMigration
{
    public function up()
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $this->table('merchants')->insert([
            'name' => 'Contorion',
            'available_financing_limit' => 2000000,
            'api_key' => 'billie',
            'roles' => '["ROLE_API_USER"]',
            'is_active' => true,
            'company_id' => 4,
            'created_at' => $now,
            'updated_at' => $now,
        ])->saveData();
    }
}
