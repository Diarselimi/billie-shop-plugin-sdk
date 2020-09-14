<?php

namespace App\DomainEvent\Order;

use App\Amqp\Producer\DelayedMessageProducer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderRiskCheck\CheckResult;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareInterface;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareTrait;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageAttachmentField;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderAfterStateChangeEventSubscriber implements EventSubscriberInterface, LoggingInterface, SlackClientAwareInterface
{
    use LoggingTrait, SlackClientAwareTrait;

    private const PRE_WAITING_STATE_QUEUE_ROUTING_KEY = 'order_in_pre_waiting_state_paella';

    private const AUTHORIZED_STATE_QUEUE_ROUTING_KEY = 'order_in_authorized_state_paella';

    private const WAITING_STATE_QUEUE_ROUTING_KEY = 'order_in_waiting_state_paella';

    private $orderRepository;

    private $notificationScheduler;

    private $delayedMessageProducer;

    private $orderEventPayloadFactory;

    private $orderRiskCheckRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        NotificationScheduler $notificationScheduler,
        DelayedMessageProducer $delayedMessageProducer,
        OrderEventPayloadFactory $orderEventPayloadFactory,
        OrderRiskCheckRepositoryInterface $orderRiskCheckRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->notificationScheduler = $notificationScheduler;
        $this->delayedMessageProducer = $delayedMessageProducer;
        $this->orderEventPayloadFactory = $orderEventPayloadFactory;
        $this->orderRiskCheckRepository = $orderRiskCheckRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            OrderApprovedEvent::class => 'onApproved',
            OrderDeclinedEvent::class => 'onDeclined',
            OrderInWaitingStateEvent::class => 'onWaiting',
            OrderShippedEvent::class => 'onShipped',
            OrderPaidOutEvent::class => 'onPaidOut',
            OrderIsLateEvent::class => 'onLate',
            OrderCanceledEvent::class => 'onCancel',
            OrderInPreWaitingStateEvent::class => 'onPreWaiting',
            OrderAuthorizedEvent::class => 'onAuthorized',
        ];
    }

    public function onApproved(OrderApprovedEvent $event): void
    {
        if ($event->isNotifyWebhook()) {
            $this->notifyMerchantWebhook(
                $event->getOrderContainer()->getOrder(),
                OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_APPROVED
            );
        }

        $merchantDebtor = $event->getOrderContainer()->getMerchantDebtor();
        $firstApprovedOrder = $this->orderRepository->merchantDebtorHasAtLeastOneApprovedOrder($merchantDebtor->getId());

        $this->logInfo("Order approved!", [
            LoggingInterface::KEY_SOBAKA => [
                'debtor_is_new' => !$firstApprovedOrder,
                'debtor_created_in_this_hour' => $merchantDebtor->getCreatedAt() > new \Datetime(date('Y-m-d H:00:00')),
                'debtor_created_today' => $merchantDebtor->getCreatedAt() > new \Datetime(date('Y-m-d 00:00:00')),
            ],
        ]);
    }

    public function onDeclined(OrderDeclinedEvent $event): void
    {
        if ($event->isNotifyWebhook()) {
            $this->notifyMerchantWebhook(
                $event->getOrderContainer()->getOrder(),
                OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_DECLINED
            );
        }

        $this->logInfo("Order declined");
    }

    public function onWaiting(OrderInWaitingStateEvent $event): void
    {
        $order = $event->getOrderContainer()->getOrder();

        $this->delayedMessageProducer->produce(
            self::WAITING_STATE_QUEUE_ROUTING_KEY,
            ['order_id' => $order->getUuid(), 'merchant_id' => $order->getMerchantId()],
            OrderEntity::MAX_DURATION_IN_WAITING_STATE
        );

        $this->notifyMerchantWebhook(
            $event->getOrderContainer()->getOrder(),
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_WAITING
        );

        $failedRiskCheckNames = array_map(
            function (CheckResult $result) {
                return $result->getName();
            },
            $event->getOrderContainer()->getRiskCheckResultCollection()->getAllDeclined()
        );

        $message = $this->getSlackMessageFactory()->createSimple(
            'Order was created in waiting state',
            "Order *{$order->getUuid()}* was created in waiting state because of failed risk checks",
            null,
            new SlackMessageAttachmentField('Merchant ID', $order->getMerchantId()),
            new SlackMessageAttachmentField('Order UUID', $order->getUuid()),
            new SlackMessageAttachmentField('Failed Risk Checks', implode(', ', $failedRiskCheckNames)),
            new SlackMessageAttachmentField('Environment', str_replace('_', '', getenv('INSTANCE_SUFFIX')))
        );

        $this->getSlackClient()->sendMessage($message);
    }

    public function onPreWaiting(OrderInPreWaitingStateEvent $event): void
    {
        $order = $event->getOrderContainer()->getOrder();

        $this->delayedMessageProducer->produce(
            self::PRE_WAITING_STATE_QUEUE_ROUTING_KEY,
            ['order_id' => $order->getUuid(), 'merchant_id' => $order->getMerchantId()],
            OrderEntity::MAX_DURATION_IN_PRE_WAITING_STATE
        );
    }

    public function onAuthorized(OrderAuthorizedEvent $event): void
    {
        $order = $event->getOrderContainer()->getOrder();

        $this->delayedMessageProducer->produce(
            self::AUTHORIZED_STATE_QUEUE_ROUTING_KEY,
            ['order_id' => $order->getUuid(), 'merchant_id' => $order->getMerchantId()],
            OrderEntity::MAX_DURATION_IN_AUTHORIZED_STATE
        );
    }

    public function onShipped(OrderShippedEvent $event): void
    {
        $this->notifyMerchantWebhook(
            $event->getOrderContainer()->getOrder(),
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_SHIPPED
        );
    }

    public function onLate(OrderIsLateEvent $event): void
    {
        $this->notifyMerchantWebhook(
            $event->getOrderContainer()->getOrder(),
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_LATE
        );
    }

    public function onPaidOut(OrderPaidOutEvent $event): void
    {
        $this->notifyMerchantWebhook(
            $event->getOrderContainer()->getOrder(),
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_PAID_OUT
        );
    }

    public function onCancel(OrderCanceledEvent $event): void
    {
        $this->notifyMerchantWebhook(
            $event->getOrderContainer()->getOrder(),
            OrderNotificationEntity::NOTIFICATION_TYPE_ORDER_CANCELED
        );
    }

    private function notifyMerchantWebhook(OrderEntity $order, string $notificationType): void
    {
        $this->notificationScheduler->createAndSchedule(
            $order,
            $notificationType,
            $this->orderEventPayloadFactory->create($order, $notificationType)
        );
    }
}
