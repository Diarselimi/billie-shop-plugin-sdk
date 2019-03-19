<?php

namespace App\DomainModel\OrderRiskCheck;

use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;

class RiskCheckDefinitionEntity extends AbstractTimestampableEntity
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
