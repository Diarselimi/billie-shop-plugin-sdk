<?php

namespace App\DomainModel\RiskCheck;

class RiskCheckEntityFactory
{
    public function create(int $orderId, int $checkId, bool $isPassed): RiskCheckEntity
    {
        return (new RiskCheckEntity())
            ->setOrderId($orderId)
            ->setCheckId($checkId)
            ->setIsPassed($isPassed)
        ;
    }
}
