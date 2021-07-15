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

    public function createAndSchedule(OrderEntity $order, ?string $invoiceUuid, string $notificationType, array $payload): bool
    {
        if (!$this->isNotificationEnabledForMerchant($order->getMerchantId(), $notificationType)) {
            return false;
        }

        $orderNotification = $this->orderNotificationFactory->create($order->getId(), $invoiceUuid, $notificationType, $payload);
        $this->orderNotificationRepository->insert($orderNotification);

        $this->logInfo('Created notification {count} for order {id}', [
            LoggingInterface::KEY_COUNT => $orderNotification->getId(),
            LoggingInterface::KEY_ID => $order->getId(),
        ]);

        return $this->schedule($orderNotification);
    }

    public function schedule(OrderNotificationEntity $orderNotification): bool
    {
        $attemptNumber = count($orderNotification->getDeliveries());
        if (!array_key_exists($attemptNumber, self::DELAY_MATRIX)) {
            $this->logInfo('Max attempt reached, no scheduling', [
                LoggingInterface::KEY_ID => $orderNotification->getId(),
                LoggingInterface::KEY_NUMBER => $attemptNumber,
            ]);

            $this->sendSlackMessage($orderNotification);

            return false;
        }

        $delay = self::DELAY_MATRIX[$attemptNumber];
        $this->logInfo('Scheduling notification {id} for execution at {date}', [
            LoggingInterface::KEY_ID => $orderNotification->getId(),
            LoggingInterface::KEY_DATE => (new \DateTime($delay))->format(\DATE_ATOM),
            LoggingInterface::KEY_SOBAKA => [
                'delay' => $delay,
                'attempt' => $attemptNumber,
                'notification_id' => $orderNotification->getId(),
            ],
        ]);

        return $this->orderNotificationFactoryPublisher->publish(['notification_id' => $orderNotification->getId()], $delay);
    }

    private function sendSlackMessage(OrderNotificationEntity $orderNotification): void
    {
        $message = $this->slackMessageFactory->createSimpleWithServiceInfo(
            self::SLACK_NOTIFICATION_TITLE,
            self::SLACK_NOTIFICATION_MESSAGE,
            null,
            new SlackMessageAttachmentField('Order ID', $orderNotification->getOrderId(), true),
            new SlackMessageAttachmentField('Invoice ID', $orderNotification->getInvoiceUuid() ?? '-', true),
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
