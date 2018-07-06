<?php

namespace App\DomainModel\RiskCheck\Checker;

class CheckResult
{
    private $isPassed;
    private $name;
    private $attributes;

    public function __construct(bool $isPassed, string $name, array $attributes)
    {
        $this->isPassed = $isPassed;
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public function isPassed(): bool
    {
        return $this->isPassed;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
