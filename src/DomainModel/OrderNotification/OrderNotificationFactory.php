<?php

namespace App\DomainModel\OrderNotification;

class OrderNotificationFactory
{
    public function create(int $orderId, string $notificationType, array $payload): OrderNotificationEntity
    {
        return (new OrderNotificationEntity())
            ->setOrderId($orderId)
            ->setNotificationType($notificationType)
            ->setPayload($payload)
            ->setIsDelivered(false)
            ->setDeliveries([])
        ;
    }

    /**
     * @return OrderNotificationEntity[]
     */
    public function createMultipleFromDatabaseRows(array $rows): array
    {
        return array_map([$this, 'createFromDatabaseRow'], $rows);
    }

    public function createFromDatabaseRow(array $row): OrderNotificationEntity
    {
        return (new OrderNotificationEntity())
            ->setId($row['id'])
            ->setOrderId($row['order_id'])
            ->setNotificationType($row['notification_type'])
            ->setPayload(json_decode($row['payload'], true))
            ->setIsDelivered(boolval($row['is_delivered']))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
