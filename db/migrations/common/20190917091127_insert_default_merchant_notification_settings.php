<?php

use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsFactory;
use App\Infrastructure\Repository\MerchantNotificationSettingsRepository;
use Phinx\Migration\AbstractMigration;

class InsertDefaultMerchantNotificationSettings extends AbstractMigration
{
    public function up()
    {
        foreach (MerchantNotificationSettingsFactory::DEFAULT_SETTINGS as $notificationType => $isEnabled) {
            $isEnabled = (int) $isEnabled;
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
