<?php

namespace App\DomainModel\Order\Workflow;

use App\Amqp\Producer\DelayedMessageProducer;
use App\DomainModel\Order\OrderEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class MessageProducerOrderWorkflowSubscriber implements EventSubscriberInterface
{
    private const PRE_WAITING_STATE_QUEUE_ROUTING_KEY = 'order_in_pre_waiting_state_paella';

    private const AUTHORIZED_STATE_QUEUE_ROUTING_KEY = 'order_in_authorized_state_paella';

    private const WAITING_STATE_QUEUE_ROUTING_KEY = 'order_in_waiting_state_paella';

    private DelayedMessageProducer $delayedMessageProducer;

    public function __construct(DelayedMessageProducer $delayedMessageProducer)
    {
        $this->delayedMessageProducer = $delayedMessageProducer;
    }

    public function onWaiting(Event $event): void
    {
        $this->produceMessage(
            $event->getSubject(),
            self::WAITING_STATE_QUEUE_ROUTING_KEY,
            OrderEntity::MAX_DURATION_IN_WAITING_STATE
        );
    }

    public function onPreWaiting(Event $event): void
    {
        $this->produceMessage(
            $event->getSubject(),
            self::PRE_WAITING_STATE_QUEUE_ROUTING_KEY,
            OrderEntity::MAX_DURATION_IN_PRE_WAITING_STATE
        );
    }

    public function onAuthorized(Event $event): void
    {
        $this->produceMessage(
            $event->getSubject(),
            self::AUTHORIZED_STATE_QUEUE_ROUTING_KEY,
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

    private function produceMessage(OrderEntity $order, string $routingKey, string $delay): void
    {
        $this->delayedMessageProducer->produce(
            $routingKey,
            ['order_id' => $order->getUuid(), 'merchant_id' => $order->getMerchantId()],
            $delay
        );
    }
}
