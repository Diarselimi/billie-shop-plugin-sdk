<?php

namespace App\DomainModel\OrderRiskCheck;

class CheckResult
{
    private $isPassed;

    private $name;

    private $declineOnFailure;

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

    public function isDeclineOnFailure(): ?bool
    {
        return $this->declineOnFailure;
    }

    public function setDeclineOnFailure(bool $declineOnFailure): self
    {
        $this->declineOnFailure = $declineOnFailure;

        return $this;
    }
}
