<?php

namespace App\DomainModel\RiskCheck;

interface RiskCheckRepositoryInterface
{
    public function insert(RiskCheckEntity $riskCheck): void;
    public function update(RiskCheckEntity $riskCheck): void;
    public function getOneById(int $id):? RiskCheckEntity;
    public function getOneByName(int $orderId, string $name):? RiskCheckEntity;
}
