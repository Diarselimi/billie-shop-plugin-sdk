<?php

namespace App\DomainModel\OrderRiskCheck;

interface RiskCheckDefinitionRepositoryInterface
{
    public function insert(RiskCheckDefinitionEntity $riskCheckDefinitionEntity): void;

    public function getByName(string $name): ? RiskCheckDefinitionEntity;
}
