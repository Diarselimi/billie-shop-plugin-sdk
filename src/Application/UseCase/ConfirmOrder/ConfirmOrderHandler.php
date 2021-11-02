<?php

namespace App\Application\UseCase\ConfirmOrder;

use App\Application\CommandHandler;
use App\DomainModel\Order\Lifecycle\ApproveOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;

class ConfirmOrderHandler implements CommandHandler
{
    private OrderContainerFactory $orderContainerFactory;

    private ApproveOrderService $approveOrderService;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        ApproveOrderService $approveOrderService
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->approveOrderService = $approveOrderService;
    }

    public function execute(ConfirmOrder $command): void
    {
        $orderContainer = $this->orderContainerFactory->loadByUuid($command->orderId());

        $this->approveOrderService->approve($orderContainer);
    }
}
