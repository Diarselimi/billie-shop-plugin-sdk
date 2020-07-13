<?php

namespace App\DomainModel\OrderRiskCheck;

class RiskCheckDefinitionEntityFactory
{
    public function create(int $id, string $name, \DateTime $createdAt, \DateTime $updatedAt): RiskCheckDefinitionEntity
    {
        return (new RiskCheckDefinitionEntity())
            ->setId($id)
            ->setName($name)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt)
        ;
    }

    public function createFromDatabaseRows(array $collection): array
    {
        return array_map([$this, 'createFromDatabaseRow'], $collection);
    }

    public function createFromDatabaseRow(array $row): RiskCheckDefinitionEntity
    {
        return (new RiskCheckDefinitionEntity())
            ->setId((int) $row['id'])
            ->setName($row['name'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
