<?php

namespace App\DomainModel\OrderNotification;

interface OrderNotificationRepositoryInterface
{
    public function insert(OrderNotificationEntity $orderNotification): void;

    public function update(OrderNotificationEntity $orderNotification): void;

    public function getOneById(int $id): ? OrderNotificationEntity;

    /**
     * @return OrderNotificationEntity[]
     */
    public function getFailedByOrderId(int $orderId): array;
}
