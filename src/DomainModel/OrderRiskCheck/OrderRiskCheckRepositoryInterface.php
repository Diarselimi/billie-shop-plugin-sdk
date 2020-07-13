<?php

namespace App\DomainModel\OrderRiskCheck;

interface OrderRiskCheckRepositoryInterface
{
    public function insert(OrderRiskCheckEntity $riskCheck): void;

    public function findByOrderAndCheckName(int $orderId, string $checkName): ?OrderRiskCheckEntity;

    public function findLastFailedRiskChecksByOrderId(int $orderId): CheckResultCollection;
}
