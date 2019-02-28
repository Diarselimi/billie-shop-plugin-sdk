<?php

namespace App\DomainModel\OrderRiskCheck;

use App\DomainModel\AbstractEntity;

class RiskCheckDefinitionEntity extends AbstractEntity
{
    private $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): RiskCheckDefinitionEntity
    {
        $this->name = $name;

        return $this;
    }
}
