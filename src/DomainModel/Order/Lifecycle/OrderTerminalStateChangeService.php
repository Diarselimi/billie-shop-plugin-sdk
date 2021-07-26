<?php

declare(strict_types=1);

namespace App\DomainModel\Order\Lifecycle;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Registry;

class OrderTerminalStateChangeService implements LoggingInterface
{
    use LoggingTrait;

    private OrderRepositoryInterface $orderRepository;

    private Registry $workflowRegistry;

    public function __construct(OrderRepositoryInterface $orderRepository, Registry $workflowRegistry)
    {
        $this->orderRepository = $orderRepository;
        $this->workflowRegistry = $workflowRegistry;
    }

    public function execute(OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        $workFlow = $this->workflowRegistry->get($order);

        $this->logInfo(sprintf('Order is in state %s', $order->getState()));

        if ($workFlow->can($order, OrderEntity::TRANSITION_CANCEL)) {
            $workFlow->apply($order, OrderEntity::TRANSITION_CANCEL);
        }

        if ($workFlow->can($order, OrderEntity::TRANSITION_CANCEL_SHIPPED)) {
            $workFlow->apply($order, OrderEntity::TRANSITION_CANCEL_SHIPPED);
        }

        if ($workFlow->can($order, OrderEntity::TRANSITION_COMPLETE)) {
            $workFlow->apply($order, OrderEntity::TRANSITION_COMPLETE);
        }
    }
}
