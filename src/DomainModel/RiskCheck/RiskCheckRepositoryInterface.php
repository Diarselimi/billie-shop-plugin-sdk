<?php

namespace App\DomainModel\RiskCheck;

interface RiskCheckRepositoryInterface
{
    public function insert(RiskCheckEntity $riskCheck): void;
}
