<?php

namespace App\DomainModel\OrderNotification;

class OrderNotificationFactory
{
    public function create(?int $orderId, ?int $invoiceUuid, string $notificationType, array $payload): OrderNotificationEntity
    {
        return (new OrderNotificationEntity())
            ->setCreatedAt(new \DateTime($payload['created_at']))
            ->setOrderId($orderId)
            ->setInvoiceUuid($invoiceUuid)
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
            ->setInvoiceUuid($row['invoice_uuid'])
            ->setNotificationType($row['notification_type'])
            ->setPayload(json_decode($row['payload'], true))
            ->setIsDelivered(boolval($row['is_delivered']))
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
