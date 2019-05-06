<?php

namespace spec\App\DomainModel\OrderNotification;

use App\DomainModel\OrderNotification\NotificationPublisherInterface;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderNotification\OrderNotificationDeliveryEntity;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationFactory;
use App\DomainModel\OrderNotification\OrderNotificationRepositoryInterface;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClient;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessage;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageAttachmentField;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageFactory;
use PhpSpec\ObjectBehavior;
use Psr\Log\NullLogger;

class NotificationSchedulerSpec extends ObjectBehavior
{
    const NOTIFICATION_ID = 200;

    const ORDER_ID = 156;

    const MERCHANT_ID = 78;

    const MERCHANT_URL = 'http://google.es/';

    const MERCHANT_AUTHORISATION = 'API-KEY';

    const NOTIFICATION_PAYLOAD = ['foo' => 'bar'];

    const DELIVERY_RESULT_CODE = 200;

    const DELIVERY_RESULT_BODY = 'body';

    public function let(
        NotificationPublisherInterface $orderNotificationFactoryPublisher,
        OrderNotificationFactory $orderNotificationFactory,
        OrderNotificationRepositoryInterface $orderNotificationRepository,
        SlackClient $slackClient,
        SlackMessageFactory $slackMessageFactory,
        RavenClient $sentry,
        OrderNotificationEntity $orderNotification
    ) {
        $this->beConstructedWith(
            $orderNotificationFactoryPublisher,
            $orderNotificationFactory,
            $orderNotificationRepository,
            $slackMessageFactory
        );

        $this->mockOrderNotification($orderNotification);

        $this->setLogger(new NullLogger())->setSentry($sentry);
        $this->setSlackClient($slackClient);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(NotificationScheduler::class);
    }

    public function it_schedules_the_notification_according_to_the_escalation_matrix(
        NotificationPublisherInterface $orderNotificationFactoryPublisher,
        OrderNotificationEntity $orderNotification
    ) {
        $orderNotification
            ->getDeliveries()
            ->willReturn($this->generateDeliveries(1))
        ;

        $payload = ['notification_id' => self::NOTIFICATION_ID];

        $orderNotificationFactoryPublisher
            ->publish($payload, NotificationScheduler::DELAY_MATRIX[1])
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $result = $this->schedule($orderNotification);
        $result->shouldBe(true);
    }

    public function it_sends_slack_message_if_max_attempts_has_been_reached(
        SlackMessageFactory $slackMessageFactory,
        SlackClient $slackClient,
        OrderNotificationEntity $orderNotification,
        SlackMessage $slackMessage
    ) {
        $orderNotification
            ->getDeliveries()
            ->willReturn($this->generateDeliveries(count(NotificationScheduler::DELAY_MATRIX)))
        ;

        $slackMessageFactory
            ->createSimpleWithServiceInfo(
                NotificationScheduler::SLACK_NOTIFICATION_TITLE,
                NotificationScheduler::SLACK_NOTIFICATION_MESSAGE,
                null,
                new SlackMessageAttachmentField('Order ID', self::ORDER_ID, true),
                new SlackMessageAttachmentField('Notification ID', self::NOTIFICATION_ID, true)
            )
            ->shouldBeCalled()
            ->willReturn($slackMessage)
        ;

        $slackClient->sendMessage($slackMessage)->shouldBeCalled();

        $result = $this->schedule($orderNotification);
        $result->shouldBe(false);
    }

    private function generateDeliveries(int $count): array
    {
        $deliveries = [];

        for ($i = 0; $i < $count; $i++) {
            $deliveries[] = new OrderNotificationDeliveryEntity();
        }

        return $deliveries;
    }

    private function mockOrderNotification(OrderNotificationEntity $notification)
    {
        $notification->getId()->willReturn(self::NOTIFICATION_ID);
        $notification->getOrderId()->willReturn(self::ORDER_ID);
        $notification->getPayload()->willReturn(self::NOTIFICATION_PAYLOAD);
        $notification->isDelivered()->willReturn(false);
    }
}
