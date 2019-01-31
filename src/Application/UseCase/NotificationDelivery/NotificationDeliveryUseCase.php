<?php

namespace App\Application\UseCase\NotificationDelivery;

use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderNotification\Exception\NotificationSenderException;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderNotification\NotificationSenderInterface;
use App\DomainModel\OrderNotification\OrderNotificationDeliveryFactory;
use App\DomainModel\OrderNotification\OrderNotificationRepositoryInterface;

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
                new NotificationDeliveryException('Notification url not set for merchant '.$merchant->getId()),
                'Exception while delivering notification, url not set'
            );

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

        if ($this->notificationScheduler->schedule($notification)) {
            $this->logInfo('Notification successfully scheduled for retry');

            return;
        }

        $this->logSuppressedException(
            new NotificationDeliveryException('Notification delivery reschedule failed'),
            "Can't reschedule notification delivery"
        );
    }
}
