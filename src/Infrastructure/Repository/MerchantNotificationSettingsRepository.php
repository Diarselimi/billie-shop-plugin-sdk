<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsEntity;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsFactory;
use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class MerchantNotificationSettingsRepository extends AbstractPdoRepository implements MerchantNotificationSettingsRepositoryInterface
{
    public const TABLE_NAME = 'merchant_notification_settings';

    private const SELECT_FIELDS = ['id', 'merchant_id', 'notification_type', 'enabled', 'created_at', 'updated_at'];

    private $factory;

    public function __construct(MerchantNotificationSettingsFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(MerchantNotificationSettingsEntity $merchantNotificationSettingsEntity): void
    {
        $id = $this->doInsert(
            'INSERT INTO ' . self::TABLE_NAME . ' 
            (`merchant_id`,`notification_type`,`enabled`,`created_at`,`updated_at`)
            VALUES (:merchant_id, :notification_type, :enabled, :created_at, :updated_at)
            ',
            [
                'merchant_id' => $merchantNotificationSettingsEntity->getMerchantId(),
                'notification_type' => $merchantNotificationSettingsEntity->getNotificationType(),
                'enabled' => (int) $merchantNotificationSettingsEntity->isEnabled(),
                'created_at' => $merchantNotificationSettingsEntity->getCreatedAt()->format(self::DATE_FORMAT),
                'updated_at' => $merchantNotificationSettingsEntity->getUpdatedAt()->format(self::DATE_FORMAT),
            ]
        );

        $merchantNotificationSettingsEntity->setId($id);
    }

    public function getByMerchantIdAndNotificationType(
        int $merchantId,
        string $notificationType
    ): ? MerchantNotificationSettingsEntity {
        $row = $this->doFetchOne(
            'SELECT ' . implode(', ', self::SELECT_FIELDS) . ' FROM ' . self::TABLE_NAME . ' 
            WHERE merchant_id = :merchant_id AND notification_type = :notificationType
        ',
            [
                'merchant_id' => $merchantId,
                'notificationType' => $notificationType,
            ]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
