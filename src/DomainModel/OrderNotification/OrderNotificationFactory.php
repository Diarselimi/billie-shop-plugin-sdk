<?php

namespace App\DomainModel\OrderNotification;

class OrderNotificationFactory
{
    public function create(int $orderId, array $payload): OrderNotificationEntity
    {
        return (new OrderNotificationEntity())
            ->setOrderId($orderId)
            ->setPayload($payload)
            ->setIsDelivered(false)
            ->setDeliveries([])
        ;
    }

    public function createFromDatabaseRow(array $row): OrderNotificationEntity
    {
        return (new OrderNotificationEntity())
            ->setId($row['id'])
            ->setOrderId($row['order_id'])
            ->setPayload(json_decode($row['payload'], true))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
