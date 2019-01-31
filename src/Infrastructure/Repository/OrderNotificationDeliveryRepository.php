<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderNotification\OrderNotificationDeliveryEntity;
use App\DomainModel\OrderNotification\OrderNotificationDeliveryFactory;
use App\DomainModel\OrderNotification\OrderNotificationDeliveryRepositoryInterface;

class OrderNotificationDeliveryRepository extends AbstractRepository implements OrderNotificationDeliveryRepositoryInterface
{
    const SELECT_FIELDS = 'id, order_notification_id, url, response_code, response_body, created_at, updated_at';

    private $factory;

    public function __construct(OrderNotificationDeliveryFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(OrderNotificationDeliveryEntity $delivery): void
    {
        $id = $this->doInsert('
            INSERT INTO order_notification_deliveries
            (order_notification_id, url, response_code, response_body, created_at, updated_at)
            VALUES
            (:order_notification_id, :url, :response_code, :response_body, :created_at, :updated_at)
        ', [
            'order_notification_id' => $delivery->getOrderNotificationId(),
            'url' => $delivery->getUrl(),
            'response_code' => $delivery->getResponseCode(),
            'response_body' => $delivery->getResponseBody(),
            'created_at' => $delivery->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $delivery->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $delivery->setId($id);
    }

    public function getAllByNotificationId(int $notificationId): array
    {
        $rows = $this->doFetchMultiple('
          SELECT ' . self::SELECT_FIELDS . '
          FROM order_notification_deliveries
          WHERE order_notification_id = :notification_id
        ', ['notification_id' => $notificationId]);

        return $rows ? $this->factory->createFromMultipleDatabaseRows($rows) : [];
    }
}
