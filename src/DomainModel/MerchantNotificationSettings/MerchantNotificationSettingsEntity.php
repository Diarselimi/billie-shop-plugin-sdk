<?php

namespace App\DomainModel\MerchantNotificationSettings;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class MerchantNotificationSettingsEntity extends AbstractTimestampableEntity
{
    private $merchantId;

    private $notificationType;

    private $enabled;

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): MerchantNotificationSettingsEntity
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getNotificationType(): string
    {
        return $this->notificationType;
    }

    public function setNotificationType(string $notificationType): MerchantNotificationSettingsEntity
    {
        $this->notificationType = $notificationType;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): MerchantNotificationSettingsEntity
    {
        $this->enabled = $enabled;

        return $this;
    }
}
