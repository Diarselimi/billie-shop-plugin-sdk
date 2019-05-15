<?php

namespace App\Application\UseCase\ApproveOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderPersistenceService;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\OrderStateManager;

class ApproveOrderUseCase
{
    const NOTIFICATION_EVENT = 'order_approved';

    private $orderRepository;

    private $orderPersistenceService;

    private $orderStateManager;

    private $orderChecksRunnerService;

    private $declinedReasonsMapper;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderPersistenceService $orderPersistenceService,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderStateManager = $orderStateManager;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->declinedReasonsMapper = $declinedReasonsMapper;
    }

    public function execute(ApproveOrderRequest $request): void
    {
        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderId(), $request->getMerchantId());

        if (!$order) {
            throw new OrderNotFoundException();
        }

        if (!$this->orderStateManager->isWaiting($order)) {
            throw new OrderWorkflowException("Cannot approve the order. Order is not in waiting state.");
        }

        $orderContainer = $this->orderPersistenceService->createFromOrderEntity($order);

        if (!$this->orderChecksRunnerService->rerunFailedChecks($orderContainer)) {
            throw new OrderWorkflowException(
                sprintf(
                    "Cannot approve the order. failed risk checks: %s",
                    implode(', ', $this->declinedReasonsMapper->mapReasons($order))
                )
            );
        }

        $this->orderStateManager->approve($orderContainer);
    }
}
