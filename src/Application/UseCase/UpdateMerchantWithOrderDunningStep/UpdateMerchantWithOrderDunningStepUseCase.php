<?php

namespace App\Application\UseCase\UpdateMerchantWithOrderDunningStep;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Webhook\NotificationDTO;
use App\DomainModel\Webhook\NotificationSender;

class UpdateMerchantWithOrderDunningStepUseCase
{
    private $orderRepository;

    private $merchantRepository;

    private $notificationSender;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MerchantRepositoryInterface $merchantRepository,
        NotificationSender $notificationSender
    ) {
        $this->orderRepository = $orderRepository;
        $this->merchantRepository = $merchantRepository;
        $this->notificationSender = $notificationSender;
    }

    public function execute(UpdateMerchantWithOrderDunningStepRequest $request): void
    {
        $order = $this->orderRepository->getOneByUuid($request->getOrderUuid());

        if (!$order) {
            throw new OrderNotFoundException("Order with UUID: {$request->getOrderUuid()} not found");
        }

        $merchant = $this->merchantRepository->getOneById($order->getMerchantId());

        $notification = (new NotificationDTO())
            ->setEventName($request->getStep())
            ->setOrderId($order->getExternalCode())
        ;

        $this->notificationSender->send($merchant, $notification);
    }
}
