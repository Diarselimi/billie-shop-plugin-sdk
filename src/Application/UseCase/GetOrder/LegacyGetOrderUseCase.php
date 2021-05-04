<?php

namespace App\Application\UseCase\GetOrder;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\OrderResponse\LegacyOrderResponse;
use App\DomainModel\OrderResponse\LegacyOrderResponseFactory;

class LegacyGetOrderUseCase
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

    public function execute(GetOrderRequest $request): LegacyOrderResponse
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $response = $this->orderResponseFactory->create($orderContainer);

        if ($orderContainer->getOrder()->getMerchantDebtorId() !== null) {
            $response->setDebtorUuid($orderContainer->getMerchantDebtor()->getUuid());
        }

        return $response;
    }
}
