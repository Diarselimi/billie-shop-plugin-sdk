<?php

namespace App\Application\UseCase\TriggerFailedOrderNotifications;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\NotificationDelivery\NotificationDeliveryRequest;
use App\Application\UseCase\NotificationDelivery\NotificationDeliveryUseCase;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\OrderNotification\OrderNotificationRepositoryInterface;

class TriggerFailedOrderNotificationsUseCase
{
    private $orderRepository;

    private $orderNotificationRepository;

    private $notificationDeliveryUseCase;

    public function __construct(
        OrderRepository $orderRepository,
        OrderNotificationRepositoryInterface $orderNotificationRepository,
        NotificationDeliveryUseCase $notificationDeliveryUseCase
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderNotificationRepository = $orderNotificationRepository;
        $this->notificationDeliveryUseCase = $notificationDeliveryUseCase;
    }

    public function execute(TriggerFailedOrderNotificationsRequest $request): void
    {
        $order = $this->orderRepository->getOneByUuid($request->getUuid());

        if (!$order) {
            throw new OrderNotFoundException();
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
