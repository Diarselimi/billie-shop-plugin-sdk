<?php

namespace App\DomainModel\Order;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;

class OrderEntityFactory
{
    public function createFromRequest(CreateOrderRequest $request): OrderEntity
    {
        return (new OrderEntity())
            ->setAmountNet($request->getAmountNet())
            ->setAmountGross($request->getAmountGross())
            ->setAmountTax($request->getAmountTax())
            ->setDuration($request->getDuration())
            ->setExternalComment($request->getComment())
            ->setExternalCode($request->getExternalCode())
            ->setMerchantId($request->getMerchantId())
            ->setState(OrderStateManager::STATE_NEW)
        ;
    }
}
