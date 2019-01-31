<?php

use Phinx\Migration\AbstractMigration;

class AddOrderNotifications extends AbstractMigration
{
    public function change()
    {
        $this->table('order_notifications')
            ->addColumn('order_id', 'integer', ['null' => false])
            ->addColumn('payload', 'json', ['null' => false])
            ->addColumn('is_delivered', 'boolean', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('order_id', 'orders', 'id')
        ->create();

        $this->table('order_notification_deliveries')
            ->addColumn('order_notification_id', 'integer', ['null' => false])
            ->addColumn('url', 'string', ['null' => false])
            ->addColumn('response_code', 'integer', ['null' => false])
            ->addColumn('response_body', 'text', ['null' => true])
            ->addColumn('created_at', 'string', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('order_notification_id', 'order_notifications', 'id')
        ->create();
    }
}
