<?php

namespace App\Application\UseCase\MarkOrderAsLate;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\DomainModel\OrderNotification\OrderNotificationService;
use Symfony\Component\Workflow\Registry;

class MarkOrderAsLateUseCase
{
    private OrderContainerFactory $orderContainerFactory;

    private Registry $workflowRegistry;

    private OrderNotificationService $notificationService;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        Registry $workflowRegistry,
        OrderNotificationService $notificationService
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->workflowRegistry = $workflowRegistry;
        $this->notificationService = $notificationService;
    }

    public function execute(MarkOrderAsLateRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByInvoiceUuid($request->getInvoiceUuid());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException();
        }

        $order = $orderContainer->getOrder();

        $this->notificationService->notify(
            $order,
            $orderContainer->getInvoices()->get($request->getInvoiceUuid()),
            OrderNotificationEntity::NOTIFICATION_TYPE_INVOICE_LATE
        );

        // transit legacy v1 order
        $workflow = $this->workflowRegistry->get($order);
        if (!$workflow->can($order, OrderEntity::TRANSITION_LATE)) {
            return;
        }
        $workflow->apply($order, OrderEntity::TRANSITION_LATE);
    }
}
