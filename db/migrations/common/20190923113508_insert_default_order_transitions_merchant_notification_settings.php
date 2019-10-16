<?php

use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsFactory;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\Infrastructure\Repository\MerchantNotificationSettingsRepository;
use Phinx\Migration\AbstractMigration;

class InsertDefaultOrderTransitionsMerchantNotificationSettings extends AbstractMigration
{
    public function change()
    {
        $notificationTypes = [
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_WAITING,
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_SHIPPED,
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_PAID_OUT,
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_LATE,
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_CANCELED,
        ];

        foreach ($notificationTypes as $notificationType) {
            $isEnabled = (int) MerchantNotificationSettingsFactory::DEFAULT_SETTINGS[$notificationType];

            $this->execute("
                INSERT INTO " . MerchantNotificationSettingsRepository::TABLE_NAME . " 
                (`merchant_id`,`notification_type`,`enabled`,`created_at`,`updated_at`)
                SELECT 
                  id AS merchant_id,
                  '{$notificationType}' AS notification_type,
                  '{$isEnabled}' AS enabled,
                  NOW() AS created_at,
                  NOW() AS updated_at
                FROM `merchants`
            ");
        }
    }
}
