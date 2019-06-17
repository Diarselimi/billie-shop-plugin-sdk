<?php

namespace App\Application\UseCase\GetOrder;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderResponse\OrderResponse;
use App\DomainModel\OrderResponse\OrderResponseFactory;

class GetOrderUseCase
{
    private $orderContainerFactory;

    private $orderResponseFactory;

    public function __construct(
        OrderContainerFactory $orderManagerFactory,
        OrderResponseFactory $orderResponseFactory
    ) {
        $this->orderContainerFactory = $orderManagerFactory;
        $this->orderResponseFactory = $orderResponseFactory;
    }

    public function execute(GetOrderRequest $request): OrderResponse
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalId(
                $request->getMerchantId(),
                $request->getOrderId()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        return $this->orderResponseFactory->create($orderContainer);
    }
}
