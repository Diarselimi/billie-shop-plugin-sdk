<?php

namespace App\DomainModel\FraudRules;

interface FraudRuleRepositoryInterface
{
    /**
     * @return FraudRuleEntity[]
     */
    public function getAll(): array;
}
