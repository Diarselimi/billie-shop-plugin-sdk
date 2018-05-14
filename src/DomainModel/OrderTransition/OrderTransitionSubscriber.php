<?php

namespace App\DomainModel\OrderTransition;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderLifecycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class OrderTransitionSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private $transitionFactory;
    private $transitionManager;

    public function __construct(
        OrderTransitionFactory $transitionFactory,
        OrderTransitionManager $transitionManager
    ) {
        $this->transitionFactory = $transitionFactory;
        $this->transitionManager = $transitionManager;
    }

    public function onOrderTransitionCompleted(Event $event)
    {
        /** @var OrderEntity $order */
        $order = $event->getSubject();
        $transitionName = $event->getTransition()->getName();

        $transitionEntity = $this->transitionFactory->create($order->getId(), $transitionName);
        $this->transitionManager->registerNewTransition($transitionEntity);

        $this->logInfo("Order transition $transitionName executed", [
            'transition' => $event->getTransition(),
        ]);
    }

    public function onOrderUpdated(OrderLifecycleEvent $event)
    {
        $this->transitionManager->saveNewTransitions($event->getOrder());
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.order.completed' => 'onOrderTransitionCompleted',
            OrderLifecycleEvent::UPDATED => 'onOrderUpdated',
        ];
    }
}
