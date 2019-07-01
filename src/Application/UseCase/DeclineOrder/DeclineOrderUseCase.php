<?php

namespace App\Application\UseCase\DeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;

class DeclineOrderUseCase
{
    private $orderRepository;

    private $orderStateManager;

    private $orderContainerFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderStateManager $orderStateManager,
        OrderContainerFactory $orderManagerFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderStateManager = $orderStateManager;
        $this->orderContainerFactory = $orderManagerFactory;
    }

    public function execute(DeclineOrderRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalId(
                $request->getMerchantId(),
                $request->getOrderId()
            );
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
