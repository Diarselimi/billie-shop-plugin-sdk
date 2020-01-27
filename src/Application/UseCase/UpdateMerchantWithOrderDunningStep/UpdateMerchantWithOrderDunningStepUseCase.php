<?php

namespace App\Application\UseCase\UpdateMerchantWithOrderDunningStep;

use App\Application\Exception\OrderNotFoundException;
use App\DomainEvent\Order\OrderEventPayloadFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderNotification\NotificationScheduler;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class UpdateMerchantWithOrderDunningStepUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $orderRepository;

    private $notificationScheduler;

    private $orderEventPayloadFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        NotificationScheduler $notificationScheduler,
        OrderEventPayloadFactory $orderEventPayloadFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->notificationScheduler = $notificationScheduler;
        $this->orderEventPayloadFactory = $orderEventPayloadFactory;
    }

    public function execute(UpdateMerchantWithOrderDunningStepRequest $request): void
    {
        $order = $this->orderRepository->getOneByUuid($request->getOrderUuid());

        if (!$order) {
            $this->logSuppressedException(
                new OrderNotFoundException(),
                'Failed to notify merchant with order dunning step change. Order not found',
                [
                    'order_uuid' => $request->getOrderUuid(),
                    'dunning_step' => $request->getStep(),
                ]
            );

            return;
        }

        $this->notificationScheduler->createAndSchedule(
            $order,
            OrderNotificationEntity::NOTIFICATION_TYPE_DCI_COMMUNICATION,
            $this->orderEventPayloadFactory->create($order, OrderNotificationEntity::NOTIFICATION_TYPE_DCI_COMMUNICATION)
        );
    }
}
