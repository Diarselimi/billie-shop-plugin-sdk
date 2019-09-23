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
