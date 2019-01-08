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
            'roles' => '["ROLE_API_USER", "ROLE_CAN_HANDLE_INVOICES"]',
            'is_active' => true,
            'company_id' => 4,
            'payment_merchant_id' => 'b95adad7-f747-45b9-b3cb-7851c4b90fac',
            'created_at' => $now,
            'updated_at' => $now,
        ])->saveData();
    }
}
