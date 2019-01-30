<?php

namespace App\DomainModel\OrderNotification;

interface OrderNotificationDeliveryRepositoryInterface
{
    /**
     * @return OrderNotificationDeliveryEntity[]|array
     */
    public function getAllByNotificationId(int $notificationId): array;

    public function insert(OrderNotificationDeliveryEntity $delivery): void;
}
