<?php

namespace App\DomainModel\Order\Workflow;

use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class MerchantNotificationOrderWorkflowSubscriber implements EventSubscriberInterface
{
    private OrderNotificationService $orderNotificationService;

    public function __construct(OrderNotificationService $orderNotificationService)
    {
        $this->orderNotificationService = $orderNotificationService;
    }

    public function onShipped(Event $event): void
    {
        $this->orderNotificationService->notify(
            $event->getSubject(),
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_SHIPPED
        );
    }

    public function onLate(Event $event): void
    {
        $this->orderNotificationService->notify(
            $event->getSubject(),
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_LATE
        );
    }

    public function onPaidOut(Event $event): void
    {
        $this->orderNotificationService->notify(
            $event->getSubject(),
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_PAID_OUT
        );
    }

    public function onCanceled(Event $event): void
    {
        $this->orderNotificationService->notify(
            $event->getSubject(),
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_CANCELED
        );
    }

    public function onWaiting(Event $event): void
    {
        $this->orderNotificationService->notify(
            $event->getSubject(),
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_WAITING
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.order_v1.entered.shipped' => 'onShipped',
            'workflow.order_v1.entered.late' => 'onLate',
            'workflow.order_v1.entered.paid_out' => 'onPaidOut',
            'workflow.order_v1.entered.canceled' => 'onCanceled',
            'workflow.order_v1.entered.waiting' => 'onWaiting',

            'workflow.order_v2.entered.shipped' => 'onShipped',
            'workflow.order_v2.entered.canceled' => 'onCanceled',
            'workflow.order_v2.entered.waiting' => 'onWaiting',
        ];
    }
}
