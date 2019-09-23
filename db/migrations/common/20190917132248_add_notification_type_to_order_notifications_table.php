<?php

use App\DomainModel\OrderNotification\OrderNotificationEntity;
use Phinx\Migration\AbstractMigration;

class AddNotificationTypeToOrderNotificationsTable extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('order_notifications')
            ->addColumn('notification_type', 'string', ['null' => true, 'after' => 'order_id'])
            ->save()
        ;

        $this->setNotificationTypeForCurrentNotifications();

        $this
            ->table('order_notifications')
            ->changeColumn('notification_type', 'string', ['null' => false])
            ->save()
        ;
    }

    private function setNotificationTypeForCurrentNotifications()
    {
        $types = [
            OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT,
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_APPROVED,
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_DECLINED,
        ];

        foreach ($types as $type) {
            $this->execute("
                UPDATE order_notifications SET notification_type = '{$type}' WHERE payload LIKE '%{$type}%'
            ");
        }
    }
}
