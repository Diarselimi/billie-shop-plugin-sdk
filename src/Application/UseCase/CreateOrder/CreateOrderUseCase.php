<?php

namespace App\Application\UseCase\CreateOrder;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Order\OrderChecksRunnerService;
use App\DomainModel\Order\OrderPersistenceService;

class CreateOrderUseCase
{
    private $orderPersistenceService;
    private $orderChecksRunnerService;

    public function __construct(
        OrderPersistenceService $orderPersistenceService,
        OrderChecksRunnerService $orderChecksRunnerService
    ) {
        $this->orderPersistenceService = $orderPersistenceService;
        $this->orderChecksRunnerService = $orderChecksRunnerService;
    }

    public function execute(CreateOrderRequest $request)
    {
        $order = $this->orderPersistenceService->persistFromRequest($request);
        if (!$this->orderChecksRunnerService->runPreconditionChecks($order)) {
            // cancel the order

            throw new PaellaCoreCriticalException(
                'Preconditions checks failed',
                PaellaCoreCriticalException::CODE_ORDER_PRECONDITION_CHECKS_FAILED
            );
        }

        // STEP 3 - Identify debtor
        // STEP 4 - Main checks (including score)
        // STEP 5 - Registration in payments system
    }
}
