<?php

namespace App\DomainModel\OrderNotification;

class OrderNotificationDeliveryFactory
{
    public function create(
        int $orderNotificationId,
        string $url,
        int $responseCode,
        ?string $responseBody
    ): OrderNotificationDeliveryEntity {
        return (new OrderNotificationDeliveryEntity())
            ->setOrderNotificationId($orderNotificationId)
            ->setUrl($url)
            ->setResponseCode($responseCode)
            ->setResponseBody($responseBody)
        ;
    }

    /**
     * @return OrderNotificationDeliveryEntity[]|array
     */
    public function createFromMultipleDatabaseRows(array $rows): array
    {
        return array_map([$this, 'createFromDatabaseRow'], $rows);
    }

    public function createFromDatabaseRow(array $row): OrderNotificationDeliveryEntity
    {
        return (new OrderNotificationDeliveryEntity())
            ->setId($row['id'])
            ->setOrderNotificationId($row['order_notification_id'])
            ->setUrl($row['url'])
            ->setResponseCode($row['response_code'])
            ->setResponseBody($row['response_body'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
