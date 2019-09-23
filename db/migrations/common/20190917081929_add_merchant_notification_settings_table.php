<?php

use App\Infrastructure\Repository\MerchantNotificationSettingsRepository;
use Phinx\Migration\AbstractMigration;

class AddMerchantNotificationSettingsTable extends AbstractMigration
{
    public function change()
    {
        $this->table(MerchantNotificationSettingsRepository::TABLE_NAME)
             ->addColumn('merchant_id', 'integer', ['null' => false])
             ->addColumn('notification_type', 'string', ['null' => false])
             ->addColumn('enabled', 'boolean', ['default' => 0])
             ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
             ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
             ->addIndex(['merchant_id', 'notification_type'], ['unique' => true])
             ->addForeignKey('merchant_id', 'merchants', 'id')
             ->create()
        ;
    }
}
