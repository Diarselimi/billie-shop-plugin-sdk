<?php

namespace App\Application\UseCase\DeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use Symfony\Component\Workflow\Registry;

class DeclineOrderUseCase
{
    private Registry $workflowRegistry;

    private DeclineOrderService $declineOrderService;

    private OrderContainerFactory $orderContainerFactory;

    public function __construct(
        Registry $workflowRegistry,
        DeclineOrderService $declineOrderService,
        OrderContainerFactory $orderManagerFactory
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->declineOrderService = $declineOrderService;
        $this->orderContainerFactory = $orderManagerFactory;
    }

    public function execute(DeclineOrderRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByUuid($request->getUuid());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $order = $orderContainer->getOrder();

        if (!$this->workflowRegistry->get($order)->can($order, OrderEntity::TRANSITION_DECLINE)) {
            throw new WorkflowException('Cannot decline the order. Order is in \'' . $order->getState() . '\' state.');
        }

        $this->declineOrderService->decline($orderContainer);
    }
}
