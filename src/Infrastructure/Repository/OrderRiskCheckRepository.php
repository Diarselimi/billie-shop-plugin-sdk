<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntity;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckEntityFactory;
use App\DomainModel\OrderRiskCheck\OrderRiskCheckRepositoryInterface;

class OrderRiskCheckRepository extends AbstractRepository implements OrderRiskCheckRepositoryInterface
{
    private $factory;

    public function __construct(OrderRiskCheckEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(OrderRiskCheckEntity $riskCheck): void
    {
        $id = $this->doInsert('
            INSERT INTO order_risk_checks
            (order_id, risk_check_definition_id, is_passed, created_at, updated_at)
            VALUES
            (:order_id, :risk_check_definition_id, :is_passed, :created_at, :updated_at)
        ', [
            'order_id' => $riskCheck->getOrderId(),
            'risk_check_definition_id' => $riskCheck->getRiskCheckDefinition()->getId(),
            'is_passed' => (int) $riskCheck->isPassed(),
            'created_at' => $riskCheck->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $riskCheck->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $riskCheck->setId($id);
    }

    public function findByOrder(int $orderId): array
    {
        $rows = $this->doFetchMultiple(
            '
            SELECT
            order_risk_checks.id AS  risk_check_id,
            order_risk_checks.order_id,
            risk_check_definition_id,
            is_passed,
            order_risk_checks.created_at AS risk_check_created_at,
            order_risk_checks.updated_at AS risk_check_updated_at,
            risk_check_definitions.name AS risk_check_definition_name,
            risk_check_definitions.created_at AS risk_check_definitions_created_at,
            risk_check_definitions.updated_at AS risk_check_definitions_updated_at
            FROM order_risk_checks
            INNER JOIN risk_check_definitions ON risk_check_definitions.id = order_risk_checks.risk_check_definition_id
            WHERE order_id = :order_id
            ',
            ['order_id' => $orderId]
        );

        return $rows ? $this->factory->createFromMultipleDatabaseRows($rows) : [];
    }
}
