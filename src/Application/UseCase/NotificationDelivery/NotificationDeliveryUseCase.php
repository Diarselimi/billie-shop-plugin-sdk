<?php

namespace App\Application\UseCase\NotificationDelivery;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderNotification\Exception\NotificationSenderException;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderNotification\NotificationSenderInterface;
use App\DomainModel\OrderNotification\OrderNotificationDeliveryFactory;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class NotificationDeliveryUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $orderRepository;

    private $merchantRepository;

    private $notificationRepository;

    private $notificationSender;

    private $notificationScheduler;

    private $notificationDeliveryFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        OrderNotificationRepositoryInterface $notificationRepository,
        NotificationSenderInterface $notificationSender,
        NotificationScheduler $notificationScheduler,
        OrderNotificationDeliveryFactory $notificationDeliveryFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->merchantRepository = $merchantRepository;
        $this->notificationRepository = $notificationRepository;
        $this->notificationSender = $notificationSender;
        $this->notificationScheduler = $notificationScheduler;
        $this->notificationDeliveryFactory = $notificationDeliveryFactory;
    }

    public function execute(NotificationDeliveryRequest $request)
    {
        $notificationId = $request->getOrderNotificationId();
        $this->logInfo('Notification delivery request {notification_id} received', [
            'notification_id' => $notificationId,
        ]);

        $notification = $this->notificationRepository->getOneById($notificationId);
        if (!$notification) {
            $this->logSuppressedException(
                new NotificationDeliveryException('Notification not found'),
                'Notification not found'
            );

            return;
        }

        if ($notification->isDelivered()) {
            $this->logSuppressedException(
                new NotificationDeliveryException('Notification has already been delivered'),
                'Notification has already been delivered'
            );

            return;
        }

        $order = $this->orderRepository->getOneById($notification->getOrderId());
        if (!$order) {
            $this->logSuppressedException(
                new NotificationDeliveryException('Order for notification not found'),
                'Order for notification not found'
            );

            return;
        }

        $merchant = $this->merchantRepository->getOneById($order->getMerchantId());
        $url = $merchant->getWebhookUrl();

        if (!$url) {
            $this->logSuppressedException(
                new NotificationDeliveryException("Notification url not set for merchant {$merchant->getId()}"),
                'Exception while delivering notification, url not set'
            );

            return;
        }

        if (!$this->isFirstOnOrderPendingNotificationsList($notification)) {
            $this->notificationScheduler->schedule($notification);

            return;
        }

        try {
            $deliveryResult = $this->notificationSender->send(
                $url,
                $merchant->getWebhookAuthorization(),
                $notification->getPayload()
            );
        } catch (NotificationSenderException $exception) {
            // what should we do in this edge case?
            $this->logSuppressedException(
                new NotificationDeliveryException('Notification delivery failed', null, $exception),
                'Critical exception while delivering notification, no reschedule'
            );

            return;
        }

        $delivery = $this->notificationDeliveryFactory->create(
            $notificationId,
            $url,
            $deliveryResult->getResponseCode(),
            $deliveryResult->getResponseBody()
        );

        $notification
            ->addDelivery($delivery)
            ->setIsDelivered($delivery->isResponseCodeSuccessful())
        ;
        $this->notificationRepository->update($notification);

        if ($notification->isDelivered()) {
            $this->logInfo('Notification successfully delivered');

            return;
        }

        $this->notificationScheduler->schedule($notification);
    }

    private function isFirstOnOrderPendingNotificationsList(OrderNotificationEntity $orderNotification): bool
    {
        $pendingNotifications = $this->notificationRepository->getFailedByOrderId($orderNotification->getOrderId());

        return $pendingNotifications[0]->getId() === $orderNotification->getId();
    }
}
