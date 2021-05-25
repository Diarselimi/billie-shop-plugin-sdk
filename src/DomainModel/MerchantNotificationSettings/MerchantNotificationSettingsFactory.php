<?php

namespace App\DomainModel\MerchantNotificationSettings;

use App\DomainModel\OrderNotification\OrderNotificationEntity;

class MerchantNotificationSettingsFactory
{
    const DEFAULT_SETTINGS = [
        OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT => true,
        OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_APPROVED => true,
        OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_DECLINED => true,
        OrderNotificationEntity::NOTIFICATION_TYPE_DCI_COMMUNICATION => false,
        OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_WAITING => false,
        OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_SHIPPED => false,
        OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_PAID_OUT => false,
        OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_LATE => false,
        OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_CANCELED => false,
        OrderNotificationEntity::NOTIFICATION_TYPE_INVOICE_PAID_OUT => false,
        OrderNotificationEntity::NOTIFICATION_TYPE_INVOICE_LATE => false,
        OrderNotificationEntity::NOTIFICATION_TYPE_INVOICE_CANCELED => false,
    ];

    /**
     * @param int $merchantId
     *
     * @return MerchantNotificationSettingsEntity[]
     */
    public function createDefaults(int $merchantId): array
    {
        $defaultSettings = [];

        foreach (self::DEFAULT_SETTINGS as $notificationType => $isEnabled) {
            $defaultSettings[] = (new MerchantNotificationSettingsEntity)
                ->setMerchantId($merchantId)
                ->setNotificationType($notificationType)
                ->setEnabled($isEnabled)
            ;
        }

        return $defaultSettings;
    }

    public function createFromDatabaseRow(array $row): MerchantNotificationSettingsEntity
    {
        return (new MerchantNotificationSettingsEntity)
            ->setId((int) $row['id'])
            ->setMerchantId((int) $row['merchant_id'])
            ->setNotificationType($row['notification_type'])
            ->setEnabled(boolval($row['enabled']))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
