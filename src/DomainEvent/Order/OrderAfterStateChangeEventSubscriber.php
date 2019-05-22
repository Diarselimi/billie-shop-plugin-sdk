<?php

namespace App\DomainEvent\Order;

use App\Amqp\Producer\DelayedMessageProducer;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderNotification\NotificationScheduler;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareInterface;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareTrait;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageAttachmentField;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderAfterStateChangeEventSubscriber implements EventSubscriberInterface, SlackClientAwareInterface, LoggingInterface
{
    use LoggingTrait, SlackClientAwareTrait;

    private const WAITING_STATE_QUEUE_ROUTING_KEY = 'order_in_waiting_state_paella';

    private $orderRepository;

    private $notificationScheduler;

    private $delayedMessageProducer;

    private $orderDeclinedReasonsMapper;

    private $merchantDebtorLimitsService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        NotificationScheduler $notificationScheduler,
        DelayedMessageProducer $delayedMessageProducer,
        OrderDeclinedReasonsMapper $orderDeclinedReasonsMapper,
        MerchantDebtorLimitsService $merchantDebtorLimitsService
    ) {
        $this->orderRepository = $orderRepository;
        $this->notificationScheduler = $notificationScheduler;
        $this->delayedMessageProducer = $delayedMessageProducer;
        $this->orderDeclinedReasonsMapper = $orderDeclinedReasonsMapper;
        $this->merchantDebtorLimitsService = $merchantDebtorLimitsService;
    }

    public static function getSubscribedEvents()
    {
        return [
            OrderCreatedEvent::NAME => 'onCreated',
            OrderDeclinedEvent::NAME => 'onDeclined',
            OrderInWaitingStateEvent::NAME => 'onWaiting',
            OrderCompleteEvent::NAME => 'onComplete',
        ];
    }

    public function onCreated(OrderCreatedEvent $event): void
    {
        if ($event->isNotifyWebhook()) {
            $this->notifyMerchantWebhook($event->getOrderContainer()->getOrder(), $event::NAME);
        }

        $merchantDebtor = $event->getOrderContainer()->getMerchantDebtor();
        $firstApprovedOrder = $this->orderRepository->merchantDebtorHasAtLeastOneApprovedOrder($merchantDebtor->getId());

        $this->logInfo("Order approved!", [
            'debtor_is_new' => !$firstApprovedOrder,
            'debtor_created_in_this_hour' => $merchantDebtor->getCreatedAt() > new \Datetime(date('Y-m-d H:00:00')),
            'debtor_created_today' => $merchantDebtor->getCreatedAt() > new \Datetime(date('Y-m-d 00:00:00')),
        ]);
    }

    public function onDeclined(OrderDeclinedEvent $event): void
    {
        if ($event->isNotifyWebhook()) {
            $this->notifyMerchantWebhook($event->getOrderContainer()->getOrder(), $event::NAME);
        }

        $this->logInfo("Order declined");
    }

    public function onWaiting(OrderInWaitingStateEvent $event): void
    {
        $order = $event->getOrder();

        $this->delayedMessageProducer->produce(
            self::WAITING_STATE_QUEUE_ROUTING_KEY,
            ['order_id' => $order->getUuid(), 'merchant_id' => $order->getMerchantId()],
            OrderEntity::MAX_DURATION_IN_WAITING_STATE
        );

        $message = $this->getSlackMessageFactory()->createSimple(
            'Order was created in waiting state',
            "Order *{$order->getUuid()}* was created in waiting state because of failed risk checks",
            null,
            new SlackMessageAttachmentField('Merchant ID', $order->getMerchantId()),
            new SlackMessageAttachmentField('Order UUID', $order->getUuid()),
            new SlackMessageAttachmentField('Failed Risk Checks', implode(', ', $this->orderDeclinedReasonsMapper->mapReasons($order))),
            new SlackMessageAttachmentField('Environment', str_replace('_', '', getenv('INSTANCE_SUFFIX')))
        );

        $this->getSlackClient()->sendMessage($message);
    }

    public function onComplete(OrderCompleteEvent $event): void
    {
        $this->merchantDebtorLimitsService->recalculate($event->getOrderContainer());
    }

    private function notifyMerchantWebhook(OrderEntity $order, string $event): void
    {
        $this->notificationScheduler->createAndSchedule($order, [
            'event' => $event,
            'order_id' => $order->getExternalCode(),
        ]);
    }
}
