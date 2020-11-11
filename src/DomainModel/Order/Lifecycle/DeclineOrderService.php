<?php

namespace App\DomainModel\Order\Lifecycle;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationService;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class DeclineOrderService implements LoggingInterface
{
    use LoggingTrait;

    private OrderNotificationService $orderNotificationService;

    private Registry $workflowRegistry;

    public function __construct(
        Registry $workflowRegistry,
        OrderNotificationService $orderNotificationService
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->orderNotificationService = $orderNotificationService;
    }

    public function decline(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $shouldNotifyWebhook = $order->isWaiting();

        $this->workflowRegistry->get($order)->apply($order, OrderEntity::TRANSITION_DECLINE);

        if ($shouldNotifyWebhook) {
            $this->orderNotificationService->notify($order, OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_DECLINED);
        }

        $this->logInfo("Order declined");
    }
}
