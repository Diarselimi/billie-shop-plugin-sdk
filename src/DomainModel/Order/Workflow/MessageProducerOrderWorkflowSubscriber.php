<?php

namespace App\DomainModel\Order\Workflow;

use App\Amqp\Producer\DelayedMessageProducer;
use App\DomainModel\Order\DomainEvent\OrderInAuthorizedStateDomainEvent;
use App\DomainModel\Order\DomainEvent\OrderInPreWaitingStateDomainEvent;
use App\DomainModel\Order\DomainEvent\OrderInWaitingStateDomainEvent;
use App\DomainModel\Order\OrderEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class MessageProducerOrderWorkflowSubscriber implements EventSubscriberInterface
{
    private DelayedMessageProducer $delayedMessageProducer;

    public function __construct(DelayedMessageProducer $delayedMessageProducer)
    {
        $this->delayedMessageProducer = $delayedMessageProducer;
    }

    public function onWaiting(Event $event): void
    {
        /** @var OrderEntity $orderEntity */
        $orderEntity = $event->getSubject();

        $this->delayedMessageProducer->produce(
            new OrderInWaitingStateDomainEvent($orderEntity->getUuid()),
            OrderEntity::MAX_DURATION_IN_WAITING_STATE
        );
    }

    public function onPreWaiting(Event $event): void
    {
        /** @var OrderEntity $orderEntity */
        $orderEntity = $event->getSubject();

        $this->delayedMessageProducer->produce(
            new OrderInPreWaitingStateDomainEvent($orderEntity->getUuid()),
            OrderEntity::MAX_DURATION_IN_WAITING_STATE
        );
    }

    public function onAuthorized(Event $event): void
    {
        /** @var OrderEntity $orderEntity */
        $orderEntity = $event->getSubject();

        $this->delayedMessageProducer->produce(
            new OrderInAuthorizedStateDomainEvent($orderEntity->getUuid()),
            OrderEntity::MAX_DURATION_IN_AUTHORIZED_STATE
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.order_v1.entered.waiting' => 'onWaiting',
            'workflow.order_v1.entered.authorized' => 'onAuthorized',
            'workflow.order_v1.entered.pre_waiting' => 'onPreWaiting',

            'workflow.order_v2.entered.authorized' => 'onAuthorized',
            'workflow.order_v2.entered.waiting' => 'onWaiting',
            'workflow.order_v2.entered.pre_waiting' => 'onPreWaiting',
        ];
    }
}
