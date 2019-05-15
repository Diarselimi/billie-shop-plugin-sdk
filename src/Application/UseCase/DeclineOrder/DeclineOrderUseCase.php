<?php

namespace App\Application\UseCase\DeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;

class DeclineOrderUseCase
{
    const NOTIFICATION_EVENT = 'order_declined';

    private $orderRepository;

    private $orderStateManager;

    private $orderPersistenceService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        OrderPersistenceService $orderPersistenceService
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderStateManager = $orderStateManager;
        $this->orderPersistenceService = $orderPersistenceService;
    }

    public function execute(DeclineOrderRequest $request): void
    {
        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderId(), $request->getMerchantId());

        if (!$order) {
            throw new OrderNotFoundException();
        }

        if (!$this->orderStateManager->isWaiting($order)) {
            throw new OrderWorkflowException("Cannot decline the order. Order is not in waiting state.");
        }

        $orderContainer = $this->orderPersistenceService->createFromOrderEntity($order);
        $this->orderStateManager->decline($orderContainer);
    }
}
