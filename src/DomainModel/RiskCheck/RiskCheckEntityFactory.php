<?php

namespace App\DomainModel\RiskCheck;

use App\DomainModel\RiskCheck\Checker\CheckResult;

class RiskCheckEntityFactory
{
    public function create(int $orderId, ?int $checkId, string $name, bool $isPassed): RiskCheckEntity
    {
        return (new RiskCheckEntity())
            ->setOrderId($orderId)
            ->setCheckId($checkId)
            ->setName($name)
            ->setIsPassed($isPassed)
        ;
    }

    public function createFromCheckResult(CheckResult $checkResult, int $orderId): RiskCheckEntity
    {
        return (new RiskCheckEntity())
            ->setOrderId($orderId)
            ->setName($checkResult->getName())
            ->setIsPassed($checkResult->isPassed())
        ;
    }

    public function createFromDatabaseRow(array $row): RiskCheckEntity
    {
        return (new RiskCheckEntity())
            ->setId($row['id'])
            ->setOrderId($row['order_id'])
            ->setCheckId($row['check_id'])
            ->setName($row['name'])
            ->setIsPassed($row['is_passed'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }
}
