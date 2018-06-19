<?php

namespace App\Application\UseCase\SendNotification;

use App\DomainModel\Webhook\NotificationDTO;
use App\DomainModel\Webhook\NotificationSender;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Merchant\MerchantNotFoundException;

class SendNotificationUseCase
{
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        NotificationSender $notificationSender
    ) {
        $this->orderRepository = $orderRepository;
        $this->merchantRepository = $merchantRepository;
        $this->notificationSender = $notificationSender;
    }

    public function execute(SendNotificationRequest $request): void
    {
        $orderId = $request->getOrderId();

        $order = $this->orderRepository->getOneByPaymentId($orderId);
        if (is_null($order)) {
            throw new OrderNotFoundException();
        }

        $merchant = $this->merchantRepository->getOneById($order->getMerchantId());
        if (is_null($merchant)) {
            throw new MerchantNotFoundException();
        }

        $notification = (new NotificationDTO())
            ->setEventName($request->getEventName())
            ->setOrderId($order->getId())
            ->setAmount($request->getAmount())
            ->setOpenAmount($request->getOpenAmount())
            ->setUrlNotification($request->getUrlNotification())
        ;

        $this->notificationSender->send($merchant, $notification);
    }
}
