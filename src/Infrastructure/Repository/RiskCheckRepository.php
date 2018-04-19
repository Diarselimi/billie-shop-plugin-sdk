<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\RiskCheck\RiskCheckEntity;
use App\DomainModel\RiskCheck\RiskCheckRepositoryInterface;

class RiskCheckRepository extends AbstractRepository implements RiskCheckRepositoryInterface
{
    public function insert(RiskCheckEntity $riskCheck): void
    {
        $id = $this->doInsert('
            INSERT INTO risk_checks
            (order_id, check_id, is_passed, created_at, updated_at)
            VALUES
            (:order_id, :check_id, :is_passed, :created_at, :updated_at)
        ', [
            'order_id' => $riskCheck->getOrderId(),
            'check_id' => $riskCheck->getCheckId(),
            'is_passed' => $riskCheck->isPassed(),
            'created_at' => $riskCheck->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $riskCheck->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $riskCheck->setId($id);
    }
}
