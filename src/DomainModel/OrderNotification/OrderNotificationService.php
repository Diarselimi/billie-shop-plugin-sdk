<?php

namespace App\DomainModel\OrderNotification;

use App\DomainModel\Invoice\Invoice;
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

    public function notify(OrderEntity $order, ?Invoice $invoice, string $notificationType): void
    {
        $this->notificationScheduler->createAndSchedule(
            $order,
            $invoice ? $invoice->getUuid() : null,
            $notificationType,
            $this->orderNotificationPayloadFactory->create($order, $invoice, $notificationType)
        );
    }
}
