<?php

namespace App\Application\UseCase\ApproveOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderRiskCheck\Checker\LimitCheck;

class ApproveOrderUseCase
{
    private $orderContainerFactory;

    private $orderStateManager;

    private $orderChecksRunnerService;

    private $declinedReasonsMapper;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        OrderStateManager $orderStateManager,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderStateManager = $orderStateManager;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
        $this->declinedReasonsMapper = $declinedReasonsMapper;
    }

    public function execute(ApproveOrderRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByUuid($request->getUuid());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if (!$this->orderStateManager->isWaiting($orderContainer->getOrder())) {
            throw new OrderWorkflowException("Cannot approve the order. Order is not in waiting state.");
        }

        if (!$this->rerunLimitCheck($orderContainer)) {
            throw new OrderWorkflowException("Cannot approve the order. Limit check failed");
        }

        if (!$this->orderChecksRunnerService->rerunFailedChecks($orderContainer)) {
            throw new OrderWorkflowException(
                sprintf(
                    "Cannot approve the order. failed risk checks: %s",
                    implode(', ', $this->declinedReasonsMapper->mapReasons($orderContainer->getOrder()))
                )
            );
        }

        $this->orderStateManager->approve($orderContainer);
    }

    private function rerunLimitCheck(OrderContainer $orderContainer): bool
    {
        return $this->orderChecksRunnerService->rerunCheck($orderContainer, LimitCheck::NAME);
    }
}
