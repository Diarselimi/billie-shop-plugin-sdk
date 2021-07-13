<?php

namespace App\Application\UseCase\LegacyUpdateOrder;

use App\Application\Exception\OrderBeingCollectedException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderUpdate\LegacyUpdateOrderService;
use App\DomainModel\Salesforce\ClaimStateService;

class LegacyUpdateOrderUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private OrderContainerFactory $orderContainerFactory;

    private LegacyUpdateOrderService $legacyUpdateOrderService;

    private ClaimStateService $claimStateService;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        LegacyUpdateOrderService $legacyUpdateOrderService,
        ClaimStateService $claimStateService
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->legacyUpdateOrderService = $legacyUpdateOrderService;
        $this->claimStateService = $claimStateService;
    }

    public function execute(LegacyUpdateOrderRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );

            $order = $orderContainer->getOrder();
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if ($this->isOrderLateAndInCollection($order)) {
            throw new OrderBeingCollectedException();
        }

        $this->legacyUpdateOrderService->update($orderContainer, $request);
    }

    private function isOrderLateAndInCollection(OrderEntity $order): bool
    {
        return $order->isLate() && $this->claimStateService->isInCollection($order->getUuid());
    }
}
