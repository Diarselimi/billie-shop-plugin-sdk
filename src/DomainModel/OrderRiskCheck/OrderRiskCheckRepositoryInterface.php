<?php

namespace App\DomainModel\OrderRiskCheck;

use App\DomainModel\Order\OrderEntity;

interface OrderRiskCheckRepositoryInterface
{
    public function insert(OrderRiskCheckEntity $riskCheck): void;

    /**
     * @return OrderRiskCheckEntity[]|array
     */
    public function findByOrder(OrderEntity $orderEntity): array;

    public function findByOrderAndCheckName(int $orderId, string $checkName): ? OrderRiskCheckEntity;

    public function update(OrderRiskCheckEntity $orderRiskCheckEntity): void;
}
