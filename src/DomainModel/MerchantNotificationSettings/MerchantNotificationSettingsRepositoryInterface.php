<?php

namespace App\DomainModel\MerchantNotificationSettings;

interface MerchantNotificationSettingsRepositoryInterface
{
    public function insert(MerchantNotificationSettingsEntity $merchantNotificationSettingsEntity): void;

    public function getByMerchantIdAndNotificationType(
        int $merchantId,
        string $notificationType
    ): ? MerchantNotificationSettingsEntity;
}
