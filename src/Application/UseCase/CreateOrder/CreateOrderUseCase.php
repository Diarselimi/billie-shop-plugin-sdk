<?php

namespace App\Application\UseCase\CreateOrder;

use App\DomainModel\Order\OrderPersistenceService;

class CreateOrderUseCase
{
    private $orderPersistenceService;

    public function __construct(OrderPersistenceService $orderPersistenceService)
    {
        $this->orderPersistenceService = $orderPersistenceService;
    }

    public function execute(CreateOrderRequest $request)
    {
        // STEP 1 - Persist all entities
        $this->orderPersistenceService->persistFromRequest($request);

        // STEP 2 - Pre-Checks
        // STEP 3 - Identify debtor
        // STEP 4 - Main checks (including score)
        // STEP 5 - Registration in payments system
    }
}
