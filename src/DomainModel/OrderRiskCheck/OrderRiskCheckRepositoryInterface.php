<?php

namespace App\DomainModel\OrderRiskCheck;

interface OrderRiskCheckRepositoryInterface
{
    public function insert(OrderRiskCheckEntity $riskCheck): void;

    /**
     * @return OrderRiskCheckEntity[]|array
     */
    public function findByOrder(int $orderId): array;

    public function findByOrderAndCheckName(int $orderId, string $checkName): ? OrderRiskCheckEntity;

    public function update(OrderRiskCheckEntity $orderRiskCheckEntity): void;
}
