<?php

namespace App\DomainModel\OrderNotification;

use App\DomainModel\Order\OrderEntity;

class OrderNotificationService
{
    private NotificationScheduler $notificationScheduler;

    private OrderNotificationPayloadFactory $orderNotificationPayloadFactory;

    public function __construct(
        NotificationScheduler $notificationScheduler,
        OrderNotificationPayloadFactory $orderNotificationPayloadFactory
    ) {
        $this->notificationScheduler = $notificationScheduler;
        $this->orderNotificationPayloadFactory = $orderNotificationPayloadFactory;
    }

    public function notify(OrderEntity $order, string $notificationType): void
    {
        $this->notificationScheduler->createAndSchedule(
            $order,
            $notificationType,
            $this->orderNotificationPayloadFactory->create($order, $notificationType)
        );
    }
}
