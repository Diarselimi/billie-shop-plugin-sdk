<?php

namespace App\Infrastructure\Repository;

use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionEntity;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionEntityFactory;
use App\DomainModel\OrderRiskCheck\RiskCheckDefinitionRepositoryInterface;
use Billie\PdoBundle\Infrastructure\Pdo\AbstractPdoRepository;

class RiskCheckDefinitionRepository extends AbstractPdoRepository implements RiskCheckDefinitionRepositoryInterface
{
    public const TABLE_NAME = "risk_check_definitions";

    private const SELECT_FIELDS = ['id', 'name', 'created_at', 'updated_at'];

    private $factory;

    public function __construct(RiskCheckDefinitionEntityFactory $factory)
    {
        $this->factory = $factory;
    }

    public function insert(RiskCheckDefinitionEntity $riskCheckDefinitionEntity): void
    {
        $fields = self::SELECT_FIELDS;
        $id = $this->doInsert(
            $this->generateInsertQuery(self::TABLE_NAME, array_slice($fields, 1)),
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
            $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) . ' WHERE name = :name',
            ['name' => $name]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getById(int $id): ?RiskCheckDefinitionEntity
    {
        $row = $this->doFetchOne(
            $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS) . ' WHERE id = :id',
            ['id' => $id]
        );

        return $row ? $this->factory->createFromDatabaseRow($row) : null;
    }

    public function getAll(): array
    {
        $rows = $this->doFetchAll(
            $this->generateSelectQuery(self::TABLE_NAME, self::SELECT_FIELDS)
        );

        return $rows ? $this->factory->createFromDatabaseRows($rows) : [];
    }
}
