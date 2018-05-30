<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\RiskCheck\RiskCheckEntity;
use App\DomainModel\RiskCheck\RiskCheckEntityFactory;
use App\DomainModel\RiskCheck\RiskCheckRepositoryInterface;

class RiskCheckRepository extends AbstractRepository implements RiskCheckRepositoryInterface
{
    private $factory;

    public function __construct(RiskCheckEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(RiskCheckEntity $riskCheck): void
    {
        $id = $this->doInsert('
            INSERT INTO risk_checks
            (order_id, check_id, name, is_passed, created_at, updated_at)
            VALUES
            (:order_id, :check_id, :name, :is_passed, :created_at, :updated_at)
        ', [
            'order_id' => $riskCheck->getOrderId(),
            'check_id' => $riskCheck->getCheckId(),
            'name' => $riskCheck->getName(),
            'is_passed' => (int) $riskCheck->isPassed(),
            'created_at' => $riskCheck->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $riskCheck->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $riskCheck->setId($id);
    }

    public function getOneByName(int $orderId, string $name):? RiskCheckEntity
    {
        $row = $this->doFetch('
            SELECT order_id, check_id, name, is_passed, created_at, updated_at
            FROM risk_checks
            WHERE order_id = :order_id AND name = :name
        ', [
            'order_id' => $orderId,
            'name' => $name,
        ]);

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
