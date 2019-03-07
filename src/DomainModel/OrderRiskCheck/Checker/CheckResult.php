<?php

namespace App\DomainModel\OrderRiskCheck\Checker;

class CheckResult
{
    private $isPassed;

    private $name;

    public function __construct(bool $isPassed, string $name)
    {
        $this->isPassed = $isPassed;
        $this->name = $name;
    }

    public function isPassed(): bool
    {
        return $this->isPassed;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
