<?php

namespace App\DomainModel\RiskCheck;

class RiskCheckEntityFactory
{
    public function create(int $orderId, int $checkId, string $name, bool $isPassed): RiskCheckEntity
    {
        return (new RiskCheckEntity())
            ->setOrderId($orderId)
            ->setCheckId($checkId)
            ->setName($name)
            ->setIsPassed($isPassed)
        ;
    }

    public function createFromDatabaseRow(array $row): RiskCheckEntity
    {
        return (new RiskCheckEntity())
            ->setOrderId($row['order_id'])
            ->setCheckId($row['check_id'])
            ->setName($row['name'])
            ->setIsPassed($row['is_passed'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
