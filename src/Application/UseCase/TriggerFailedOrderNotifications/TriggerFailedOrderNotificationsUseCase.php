<?php

namespace App\Application\UseCase\TriggerFailedOrderNotifications;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\NotificationDelivery\NotificationDeliveryRequest;
use App\Application\UseCase\NotificationDelivery\NotificationDeliveryUseCase;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderNotification\OrderNotificationRepositoryInterface;

class TriggerFailedOrderNotificationsUseCase
{
    private $orderRepository;

    private $orderNotificationRepository;

    private $notificationDeliveryUseCase;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderNotificationRepositoryInterface $orderNotificationRepository,
        NotificationDeliveryUseCase $notificationDeliveryUseCase
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderNotificationRepository = $orderNotificationRepository;
        $this->notificationDeliveryUseCase = $notificationDeliveryUseCase;
    }

    public function execute(TriggerFailedOrderNotificationsRequest $request): void
    {
        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderId(), $request->getMerchantId());

        if (!$order) {
            throw new OrderNotFoundException("Order #{$request->getOrderId()} does't exist");
        }

        $failedNotifications = $this->orderNotificationRepository->getFailedByOrderId($order->getId());

        if (empty($failedNotifications)) {
            return;
        }

        foreach ($failedNotifications as $failedNotification) {
            $notificationDeliveryRequest = new NotificationDeliveryRequest($failedNotification->getId());
            $this->notificationDeliveryUseCase->execute($notificationDeliveryRequest);
        }
    }
}
