<?php

namespace App\DomainModel\OrderNotification;

use App\DomainModel\MerchantNotificationSettings\MerchantNotificationSettingsRepositoryInterface;
use App\DomainModel\Order\OrderEntity;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareInterface;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackClientAwareTrait;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageAttachmentField;
use Billie\MonitoringBundle\Service\Alerting\Slack\SlackMessageFactory;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class NotificationScheduler implements LoggingInterface, SlackClientAwareInterface
{
    use LoggingTrait, SlackClientAwareTrait;

    const DELAY_MATRIX = [
        0 => '1 second',
        1 => '1 second',
        2 => '5 seconds',
        3 => '1 hour',
        4 => '3 hours',
        5 => '6 hours',
        6 => '6 hours',
        7 => '6 hours',
    ];

    const SLACK_NOTIFICATION_TITLE = 'Webhook Notification Scheduler';

    const SLACK_NOTIFICATION_MESSAGE = 'Order notification reached maximum delivery attempts';

    private $orderNotificationFactoryPublisher;

    private $orderNotificationFactory;

    private $orderNotificationRepository;

    private $slackMessageFactory;

    private $merchantNotificationSettingsRepository;

    public function __construct(
        NotificationPublisherInterface $orderNotificationFactoryPublisher,
        OrderNotificationFactory $orderNotificationFactory,
        OrderNotificationRepositoryInterface $orderNotificationRepository,
        SlackMessageFactory $slackMessageFactory,
        MerchantNotificationSettingsRepositoryInterface $merchantNotificationSettingsRepository
    ) {
        $this->orderNotificationFactoryPublisher = $orderNotificationFactoryPublisher;
        $this->orderNotificationFactory = $orderNotificationFactory;
        $this->orderNotificationRepository = $orderNotificationRepository;
        $this->slackMessageFactory = $slackMessageFactory;
        $this->merchantNotificationSettingsRepository = $merchantNotificationSettingsRepository;
    }

    public function createAndSchedule(OrderEntity $order, string $notificationType, array $payload): bool
    {
        if (!$this->isNotificationEnabledForMerchant($order->getMerchantId(), $notificationType)) {
            $this->logInfo(
                'Skip sending {notification_type} notification for order {order_id}[{order_external_code}]. 
                It is disabled for merchant {merchant_id}
                ',
                [
                    'notification_type' => $notificationType,
                    'order_id' => $order->getId(),
                    'order_external_code' => $order->getExternalCode(),
                    'merchant_id' => $order->getMerchantId(),
                ]
            );

            return false;
        }

        $orderNotification = $this->orderNotificationFactory->create($order->getId(), $notificationType, $payload);
        $this->orderNotificationRepository->insert($orderNotification);

        $this->logInfo('Created notification {notification_id} for order {order_id}[{order_external_code}]', [
            'notification_type' => $notificationType,
            'notification_id' => $orderNotification->getId(),
            'order_id' => $order->getId(),
            'order_external_code' => $order->getExternalCode(),
        ]);

        return $this->schedule($orderNotification);
    }

    public function schedule(OrderNotificationEntity $orderNotification): bool
    {
        $attemptNumber = count($orderNotification->getDeliveries());
        if (!array_key_exists($attemptNumber, self::DELAY_MATRIX)) {
            $this->logInfo('Max attempt reached, no scheduling', [
                'notification_id' => $orderNotification->getId(),
                'attempt' => $attemptNumber,
            ]);

            $this->sendSlackMessage($orderNotification);

            return false;
        }

        $payload = ['notification_id' => $orderNotification->getId()];

        $this->logInfo('Scheduling notification {notification_id} for execution at {datetime}', [
            'notification_id' => $orderNotification->getId(),
            'datetime' => (new \DateTime(self::DELAY_MATRIX[$attemptNumber]))->format('Y-m-d H:i:d'),
            'attempt' => $attemptNumber,
            'payload' => json_encode($payload),
        ]);

        $result = $this->orderNotificationFactoryPublisher->publish($payload, self::DELAY_MATRIX[$attemptNumber]);

        $this->logInfo('Scheduling '.($result ? 'successful' : 'unsuccessful'), [
            'notification_id' => $orderNotification->getId(),
        ]);

        return $result;
    }

    private function sendSlackMessage(OrderNotificationEntity $orderNotification): void
    {
        $message = $this->slackMessageFactory->createSimpleWithServiceInfo(
            self::SLACK_NOTIFICATION_TITLE,
            self::SLACK_NOTIFICATION_MESSAGE,
            null,
            new SlackMessageAttachmentField('Order ID', $orderNotification->getOrderId(), true),
            new SlackMessageAttachmentField('Notification ID', $orderNotification->getId(), true)
        );

        $this->getSlackClient()->sendMessage($message);
    }

    private function isNotificationEnabledForMerchant(int $merchantId, string $notificationType): bool
    {
        $setting = $this
            ->merchantNotificationSettingsRepository
            ->getByMerchantIdAndNotificationType($merchantId, $notificationType)
        ;

        return $setting && $setting->isEnabled();
    }
}
