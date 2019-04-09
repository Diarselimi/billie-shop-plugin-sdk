<?php

namespace App\DomainEvent\Order;

use App\Amqp\Producer\DelayedMessageProducer;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderEntity;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClient;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessage;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageAttachment;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageAttachmentField;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderInWaitingStateEventSubscriber implements EventSubscriberInterface, LoggingInterface
{
    use LoggingTrait;

    private const ROUTING_KEY = 'order_in_waiting_state_paella';

    private $delayedMessageProducer;

    private $slackClient;

    private $orderDeclinedReasonsMapper;

    public function __construct(
        DelayedMessageProducer $delayedMessageProducer,
        SlackClient $slackClient,
        OrderDeclinedReasonsMapper $orderDeclinedReasonsMapper
    ) {
        $this->delayedMessageProducer = $delayedMessageProducer;
        $this->slackClient = $slackClient;
        $this->orderDeclinedReasonsMapper = $orderDeclinedReasonsMapper;
    }

    public static function getSubscribedEvents()
    {
        return [
            OrderInWaitingStateEvent::NAME => 'onOrderMovedToWaitingState',
        ];
    }

    public function onOrderMovedToWaitingState(OrderInWaitingStateEvent $event)
    {
        $this->publishToOrdersInWaitingStateQueue($event->getOrder());
        $this->sendSlackMessage($event->getOrder());
    }

    private function publishToOrdersInWaitingStateQueue(OrderEntity $order): void
    {
        $this->delayedMessageProducer->produce(
            self::ROUTING_KEY,
            ['order_id' => $order->getUuid(), 'merchant_id' => $order->getMerchantId()],
            OrderEntity::MAX_DURATION_IN_WAITING_STATE
        );
    }

    private function sendSlackMessage(OrderEntity $order): void
    {
        try {
            $text = "Order *{$order->getExternalCode()}* was created in waiting state because of failed risk checks";

            $message = (new SlackMessage())->addAttachment(
                (new SlackMessageAttachment($text))
                    ->setTitle('Order was created in waiting state')
                    ->setText($text)
                    ->addField((new SlackMessageAttachmentField('Merchant ID', $order->getMerchantId())))
                    ->addField((new SlackMessageAttachmentField(
                        'Order Code / UUID',
                        $order->getExternalCode() ?: $order->getUuid()
                    )
                    ))
                    ->addField(new SlackMessageAttachmentField(
                        'Failed Risk Checks',
                        implode(', ', $this->orderDeclinedReasonsMapper->mapReasons($order))
                    ))
                    ->addField(new SlackMessageAttachmentField(
                        'Environment',
                        str_replace('_', '', getenv('INSTANCE_SUFFIX'))
                    ))
            );

            $this->slackClient->sendMessage($message);
        } catch (\Exception $e) {
            $this->logSuppressedException($e, 'Failed to notify slack with order in waiting state notification', [
                'exception' => $e,
            ]);
        }
    }
}
