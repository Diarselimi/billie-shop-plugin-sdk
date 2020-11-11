<?php

namespace App\DomainModel\Order\Workflow;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class PersistenceOrderWorkflowSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function onTransition(Event $event): void
    {
        /** @var OrderEntity $order */
        $order = $event->getSubject();
        $this->orderRepository->update($order);

        $this->logInfo(sprintf('Order was moved to %s state', $order->getState()));
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.order_v1.entered' => ['onTransition', 255],
            'workflow.order_v2.entered' => ['onTransition', 255],
        ];
    }
}
