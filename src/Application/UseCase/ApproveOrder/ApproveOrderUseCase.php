<?php

namespace App\Application\UseCase\ApproveOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainModel\Order\Lifecycle\ApproveOrderService;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\OrderRiskCheck\Checker\DebtorIdentifiedBillingAddressCheck;
use App\DomainModel\OrderRiskCheck\Checker\DeliveryAddressCheck;
use App\DomainModel\OrderRiskCheck\Checker\FraudScoreCheck;
use App\DomainModel\OrderRiskCheck\Checker\LimitCheck;

class ApproveOrderUseCase
{
    public const RISK_CHECKS_TO_SKIP = [
        DeliveryAddressCheck::NAME,
        DebtorIdentifiedBillingAddressCheck::NAME,
        FraudScoreCheck::NAME,
        LimitCheck::NAME,
    ];

    private OrderContainerFactory $orderContainerFactory;

    private ApproveOrderService $approveOrderService;

    private OrderChecksRunnerService $orderChecksRunnerService;

    private OrderDeclinedReasonsMapper $declinedReasonsMapper;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        ApproveOrderService $approveOrderService,
        OrderChecksRunnerService $orderChecksRunnerService,
        OrderDeclinedReasonsMapper $declinedReasonsMapper
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->approveOrderService = $approveOrderService;
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

        $order = $orderContainer->getOrder();

        if (!$order->isWaiting()) {
            throw new WorkflowException("Cannot approve the order. Order is not in waiting state.");
        }

        if (!$this->orderChecksRunnerService->rerunChecks($orderContainer, [LimitCheck::NAME])) {
            throw new WorkflowException("Cannot approve the order. Limit check failed");
        }

        if (!$this->orderChecksRunnerService->rerunFailedChecks($orderContainer, self::RISK_CHECKS_TO_SKIP)) {
            $firstFailed = $orderContainer->getRiskCheckResultCollection()->getFirstDeclined();

            throw new WorkflowException(
                sprintf(
                    "Cannot approve the order. failed risk checks: %s",
                    $this->declinedReasonsMapper->mapReason($firstFailed)
                )
            );
        }

        $this->approveOrderService->approve($orderContainer);
    }
}
