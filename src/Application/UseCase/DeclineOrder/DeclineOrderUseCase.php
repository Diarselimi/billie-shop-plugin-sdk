<?php

namespace App\Application\UseCase\DeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderStateManager;

class DeclineOrderUseCase
{
    private $orderStateManager;

    private $orderContainerFactory;

    public function __construct(
        OrderStateManager $orderStateManager,
        OrderContainerFactory $orderManagerFactory
    ) {
        $this->orderStateManager = $orderStateManager;
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

        if (!$this->orderStateManager->isWaiting($order) && !$this->orderStateManager->isPreApproved($order)) {
            throw new OrderWorkflowException('Cannot decline the order. Order is not in waiting/pre_approved state.');
        }

        $this->orderStateManager->decline($orderContainer);
    }
}
