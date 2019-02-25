<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\RiskCheck\RiskCheckEntity;
use App\DomainModel\RiskCheck\RiskCheckEntityFactory;
use App\DomainModel\RiskCheck\RiskCheckRepositoryInterface;

class RiskCheckRepository extends AbstractRepository implements RiskCheckRepositoryInterface
{
    private const SELECT_FIELDS = 'id, order_id, name, is_passed, created_at, updated_at';

    private $factory;

    public function __construct(RiskCheckEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(RiskCheckEntity $riskCheck): void
    {
        $id = $this->doInsert('
            INSERT INTO risk_checks
            (order_id, name, is_passed, created_at, updated_at)
            VALUES
            (:order_id, :name, :is_passed, :created_at, :updated_at)
        ', [
            'order_id' => $riskCheck->getOrderId(),
            'name' => $riskCheck->getName(),
            'is_passed' => (int) $riskCheck->isPassed(),
            'created_at' => $riskCheck->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $riskCheck->getUpdatedAt()->format(self::DATE_FORMAT),
        ]);

        $riskCheck->setId($id);
    }

    public function getOneById(int $id): ? RiskCheckEntity
    {
        $row = $this->doFetchOne(
            'SELECT ' . self::SELECT_FIELDS .' FROM risk_checks WHERE id = :id',
            ['id' => $id]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getOneByName(int $orderId, string $name): ? RiskCheckEntity
    {
        $row = $this->doFetchOne(
            'SELECT ' . self::SELECT_FIELDS .' FROM risk_checks WHERE order_id = :order_id AND name = :name',
            ['order_id' => $orderId, 'name' => $name]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function findByOrder(int $orderId): array
    {
        $rows = $this->doFetchMultiple(
            'SELECT ' . self::SELECT_FIELDS .' FROM risk_checks WHERE order_id = :order_id',
            ['order_id' => $orderId]
        );

        return $rows ? $this->factory->createFromMultipleDatabaseRows($rows) : null;
    }
}
