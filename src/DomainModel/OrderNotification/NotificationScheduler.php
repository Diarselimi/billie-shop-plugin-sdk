<?php

namespace App\DomainModel\OrderNotification;

use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderEntity;

// TODO: cover with tests
class NotificationScheduler implements LoggingInterface
{
    use LoggingTrait;

    private const DELAY_MATRIX = [
        0 => 'PT1S',
//        1 => 'PT5M',
//        2 => 'PT1H',
//        3 => 'PT3H',
//        4 => 'PT6H',
//        5 => 'PT6H',
//        6 => 'PT6H',
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

        $delay = new \DateInterval(self::DELAY_MATRIX[$attemptNumber]);
        $payload = json_encode(['notification_id' => $orderNotification->getId()]);

        $this->logInfo('Scheduling notification {notification_id} for execution at {datetime}', [
            'notification_id' => $orderNotification->getId(),
            'datetime' => (new \DateTime())->add($delay)->format('Y-m-d H:i:d'),
            'attempt' => $attemptNumber,
            'payload' => $payload,
        ]);

        $result = $this->orderNotificationFactoryPublisher->publish($payload, $delay);

        $this->logInfo('Scheduling '.($result ? 'successful' : 'unsuccessful'), [
            'notification_id' => $orderNotification->getId(),
        ]);

        return $result;
    }
}
