<?php

namespace App\Application\UseCase\GetOrder;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderResponse\LegacyOrderResponseFactory;

class GetOrderUseCase
{
    private OrderContainerFactory $orderContainerFactory;

    private LegacyOrderResponseFactory $orderResponseFactory;

    public function __construct(
        OrderContainerFactory $orderManagerFactory,
        LegacyOrderResponseFactory $orderResponseFactory
    ) {
        $this->orderContainerFactory = $orderManagerFactory;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(GetOrderRequest $request): OrderContainer
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        return $orderContainer;
    }
}
