<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutDeclineOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use Symfony\Component\Workflow\Registry;

class CheckoutDeclineOrderUseCase
{
    private Registry $workflowRegistry;

    private DeclineOrderService $declineOrderService;

    private OrderContainerFactory $orderContainerFactory;

    private CheckoutSessionRepositoryInterface $checkoutSessionRepository;

    public function __construct(
        Registry $workflowRegistry,
        DeclineOrderService $declineOrderService,
        OrderContainerFactory $orderContainerFactory,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->declineOrderService = $declineOrderService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
    }

    /**
     * @throws OrderNotFoundException
     */
    public function execute(CheckoutDeclineOrderRequest $input): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid($input->getSessionUuid());
        } catch (OrderContainerFactoryException $e) {
            throw new OrderNotFoundException($e);
        }

        $order = $orderContainer->getOrder();
        if (!$this->workflowRegistry->get($order)->can($order, OrderEntity::TRANSITION_DECLINE)) {
            throw new  WorkflowException('Order cannot be declined.');
        }

        $this->declineOrderService->decline($orderContainer);
        $this->checkoutSessionRepository->reActivateSession($input->getSessionUuid());
    }
}
