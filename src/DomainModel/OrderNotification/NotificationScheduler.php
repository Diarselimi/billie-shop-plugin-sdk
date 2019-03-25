<?php

namespace App\DomainModel\OrderNotification;

use App\DomainModel\Order\OrderEntity;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

// TODO: cover with tests
class NotificationScheduler implements LoggingInterface
{
    use LoggingTrait;

    private const DELAY_MATRIX = [
        0 => '1 second',
//        1 => '5 minutes',
//        2 => 'q hour',
//        3 => '3 hours',
//        4 => '6 hours',
    ];

    private $orderNotificationFactoryPublisher;

    private $orderNotificationFactory;

    private $orderNotificationRepository;

    public function __construct(
        NotificationPublisherInterface $orderNotificationFactoryPublisher,
        OrderNotificationFactory $orderNotificationFactory,
        OrderNotificationRepositoryInterface $orderNotificationRepository
    ) {
        $this->orderNotificationFactoryPublisher = $orderNotificationFactoryPublisher;
        $this->orderNotificationFactory = $orderNotificationFactory;
        $this->orderNotificationRepository = $orderNotificationRepository;
    }

    public function createAndSchedule(OrderEntity $order, array $payload): bool
    {
        $orderNotification = $this->orderNotificationFactory->create($order->getId(), $payload);
        $this->orderNotificationRepository->insert($orderNotification);

        $this->logInfo('Created notification {notification_id} for order {order_id}[{order_external_code}]', [
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
}
