<?php

namespace App\DomainModel\OrderLineItem;

interface OrderLineItemRepositoryInterface
{
    public function insert(OrderLineItemEntity $orderLineItemEntity): void;

    /**
     * @return OrderLineItemEntity[]
     */
    public function getByOrderId(int $orderId): array;
}
