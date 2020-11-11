<?php

namespace App\DomainModel\Order\Lifecycle;

use App\Application\Exception\WorkflowException;
use App\DomainModel\Order\CreateOrderCrossChecksService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationService;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class ApproveOrderService implements LoggingInterface
{
    use LoggingTrait;

    private CreateOrderCrossChecksService $orderCrossChecksService;

    private OrderNotificationService $orderNotificationService;

    private Registry $workflowRegistry;

    public function __construct(
        CreateOrderCrossChecksService $orderCrossChecksService,
        Registry $workflowRegistry,
        OrderNotificationService $orderNotificationService
    ) {
        $this->orderCrossChecksService = $orderCrossChecksService;
        $this->workflowRegistry = $workflowRegistry;
        $this->orderNotificationService = $orderNotificationService;
    }

    public function approve(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $shouldNotifyWebhook = $order->isWaiting();

        try {
            $this->orderCrossChecksService->run($orderContainer);
        } catch (WorkflowException $exception) {
            $this->logSuppressedException($exception, 'Order approve failed because of cross checks');

            throw new WorkflowException('Order cannot be approved', null, $exception);
        }

        $this->workflowRegistry->get($order)->apply($order, OrderEntity::TRANSITION_CREATE);

        if ($shouldNotifyWebhook) {
            $this->orderNotificationService->notify($order, OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_APPROVED);
        }

        $this->logInfo("Order approved");
    }
}
