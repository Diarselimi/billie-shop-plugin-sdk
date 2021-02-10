<?php

namespace App\Application\UseCase\LegacyUpdateOrder;

use App\Application\Exception\OrderBeingCollectedException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\SalesforceInterface;
use App\DomainModel\OrderUpdate\LegacyUpdateOrderService;

class LegacyUpdateOrderUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private OrderContainerFactory $orderContainerFactory;

    private LegacyUpdateOrderService $legacyUpdateOrderService;

    private SalesforceInterface $salesforce;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        LegacyUpdateOrderService $legacyUpdateOrderService,
        SalesforceInterface $salesforce
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->legacyUpdateOrderService = $legacyUpdateOrderService;
        $this->salesforce = $salesforce;
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

        if ($this->isOrderLateAndInCollections($order)) {
            throw new OrderBeingCollectedException();
        }

        $this->legacyUpdateOrderService->update($orderContainer, $request);
    }

    private function isOrderLateAndInCollections(OrderEntity $order): bool
    {
        if (!$order->isLate()) {
            return false;
        }

        return $this->salesforce->getOrderCollectionsStatus($order->getUuid()) !== null;
    }
}
