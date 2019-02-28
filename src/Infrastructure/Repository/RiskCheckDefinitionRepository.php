<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionEntity;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionEntityFactory;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionRepositoryInterface;

class RiskCheckDefinitionRepository extends AbstractRepository implements RiskCheckDefinitionRepositoryInterface
{
    private $factory;

    public function __construct(RiskCheckDefinitionEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(RiskCheckDefinitionEntity $riskCheckDefinitionEntity): void
    {
        $id = $this->doInsert(
            'INSERT INTO risk_check_definitions (name, created_at, updated_at) VALUES (:name, :created_at, :updated_at)',
            [
                'name' => $riskCheckDefinitionEntity->getName(),
                'created_at' => $riskCheckDefinitionEntity->getCreatedAt()->format(self::DATE_FORMAT),
                'updated_at' => $riskCheckDefinitionEntity->getUpdatedAt()->format(self::DATE_FORMAT),
            ]
        );

        $riskCheckDefinitionEntity->setId($id);
    }

    public function getByName(string $name): ?RiskCheckDefinitionEntity
    {
        $row = $this->doFetchOne(
            'SELECT * FROM risk_check_definitions WHERE name = :name',
            ['name' => $name]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }
}
