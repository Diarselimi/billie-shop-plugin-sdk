<?php

namespace App\Application\UseCase\UpdateMerchantWithOrderDunningStep;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderNotification\NotificationScheduler;

class UpdateMerchantWithOrderDunningStepUseCase
{
    private $orderRepository;

    private $notificationScheduler;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        NotificationScheduler $notificationScheduler
    ) {
        $this->orderRepository = $orderRepository;
        $this->notificationScheduler = $notificationScheduler;
    }

    public function execute(UpdateMerchantWithOrderDunningStepRequest $request): void
    {
        $order = $this->orderRepository->getOneByUuid($request->getOrderUuid());

        if (!$order) {
            throw new OrderNotFoundException();
        }

        $payload = [
            'event' => $request->getStep(),
            'order_id' => $order->getExternalCode(),
        ];

        $this->notificationScheduler->createAndSchedule($order, $payload);
    }
}
