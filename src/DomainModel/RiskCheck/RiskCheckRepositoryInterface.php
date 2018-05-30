<?php

namespace App\DomainModel\RiskCheck;

interface RiskCheckRepositoryInterface
{
    public function insert(RiskCheckEntity $riskCheck): void;
    public function getOneByName(int $orderId, string $name):? RiskCheckEntity;
}
